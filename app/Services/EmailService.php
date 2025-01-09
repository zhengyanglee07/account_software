<?php

namespace App\Services;

use App\AffiliateUser;
use App\Email;
use App\EmailBounce;
use App\EmailStatus;
use App\EmailSuppressionList;
use App\Mail\AutomationEmail;
use App\Mail\StandardEmail;
use App\Mail\OnboardingEmail;
use App\Mail\StandardTestEmail;
use App\Models\EmailReport;
use App\ProcessedContact;
use App\ProcessedTag;
use App\Segment;
use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Traits\ReferralCampaignTrait;
use DOMDocument;
use Illuminate\Support\Carbon;

class EmailService
{
    use ReferralCampaignTrait;
    private $segmentService;
    private $emailMergeTagService;

    public function __construct(
        SegmentService $segmentService,
        EmailMergeTagService $emailMergeTagService
    ) {
        $this->segmentService = $segmentService;
        $this->emailMergeTagService = $emailMergeTagService;
    }

    // =================================================================
    // Helpers
    // =================================================================
    /**
     * Check whether there's any important info missing on email setup.
     * Return false if no info is missing, else true.
     *
     * @param \App\Email $email
     * @return bool
     */
    public function checkIfAnyEmptyEmailSetup(Email $email): bool
    {
        $target = $email->target;
        $recipientsExist = $target === 'all' || ($target === 'specific-tag' ? $email->tag_id : $email->segment_id);
        $senderExists = $email->sender_id;
        $emailDesignExists = $email->email_design_id;

        return !($recipientsExist && $senderExists && $emailDesignExists);
    }

    /**
     * Get mailer to use depends on current environment. Ease local testing
     *
     * @return string
     */
    private function getMailer(): string
    {
        return app()->environment('local') ? 'smtp' : 'ses-markt';
    }
    // =================================================================
    // End helpers
    // =================================================================

    /**
     * Format sender email address based on sender name
     * Primarily for transactional emails on behalf of customer
     *
     * @param $senderName
     * @return string
     */
    public function formatTransactionalSenderAddress($senderName): string
    {
        // use default address if company name is empty
        if (!$senderName) return 'mail@notification.hypershapes.com';

        // lower case and trim spaces
        $senderName = strtolower($senderName);
        $senderName = trim($senderName);

        // &: handle case like 'Tee & Co.'
        $senderName = str_replace([' ', '&'], ['-', 'and'], $senderName);

        // remove all chars except alpha num & -
        $senderName = preg_replace('/[^A-Za-z0-9-]/', '', $senderName);

        return "$senderName@notification.hypershapes.com";
    }

    /**
     * Wrapper around EmailMergeTagService's mergeTags
     *
     * @param \App\Email $email
     * @param \App\ProcessedContact $contact
     * @param string|null $html
     * @return string
     */
    public function mergeTags(Email $email, ProcessedContact $contact, ?string $html = null): string
    {
        return $this->emailMergeTagService->mergeTags($email, $contact, $html);
    }

    /**
     * Inject affiliate badge into email
     *
     * @param \App\Email $email
     * @return string|string[]|null MJML string with injected badge
     */
    public function injectAffiliateBadge(Email $email)
    {
        $html = $email->emailDesign->html;

        $hasAffiliateBadge = $email->account->has_email_affiliate_badge ?? true;

        if (!$hasAffiliateBadge) {
            return $html;
        }

        $user = User::where('currentAccountId', $email->account_id)->first();
        $affiliateUserEmail = optional($user)->email;

        if (!$affiliateUserEmail) {
            return $html;
        }

        $badgeHTML = '<table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;"> <tbody> <tr> <td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;"> <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"> <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%"> <tbody> <tr> <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;"> <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;"> <tbody> <tr> <td style="width:168px;"> <img height="auto" src="https://media.hypershapes.com/images/email-affiliate-badge.png" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="168"/> </td></tr></tbody> </table> </td></tr></tbody> </table> </div></td></tr></tbody> </table>';
        return str_replace('</body>', $badgeHTML . '</body>', $html);
    }

    /**
     * Send standard email from messages/emails accordingly to segment.
     * Recipients' email address is obtained from segment's contact.
     * Return true if successfully send, else false
     *
     * @param \App\Email $email
     * @return bool
     */
    public function sendStandardEmail(Email $email): bool
    {
        $accountId = $email->account_id;
        $segmentId = $email->segment_id;
        $tagId = $email->tag_id;

        if ($this->checkIfAnyEmptyEmailSetup($email)) {
            return false;
        }

        switch ($email->target) {
            case 'all':
                $contacts = ProcessedContact::whereAccountId($accountId)->get();
                break;
            case 'specific-tag':
                $contacts = optional(ProcessedTag::find($tagId))->processedContacts ?? [];
                break;
            case 'specific-segment':
                $contacts = optional(Segment::find($segmentId))->contacts() ?? [];
        }

        if (count($contacts ?? []) === 0) {
            Log::error('Send standard email failed: no contacts.');
            return false;
        }

        EmailReport::create(['email_id' => $email->id]);

        foreach ($contacts as $contact) {
            $contactEmail = $contact->email;
            $bouncedAddress = $this->checkIsBouncedEmail($accountId, $contactEmail);

            // skip any bounced email address or unsub contact
            if ($bouncedAddress || $contact->emailUnsub()->first()) {

                $email->processedContacts()->updateExistingPivot($contact->id, [
                    'status' => $bouncedAddress ? 'bounce' : 'unsubscribe',
                ]);
                continue;
            }

            // prevent error if destination email is null
            if (!$contactEmail) {
                continue;
            }

            $email->processedContacts()->attach($contact->id);

            info('Email Address:- ' . $contactEmail);

            Mail::mailer($this->getMailer())->to($contactEmail)->send(new StandardEmail($email, $contact));
        }

        // remember to update email status as well
        $sentStatus = EmailStatus::whereStatus('Sent')->firstOrFail();
        $email->update([
            'schedule' => Carbon::now(),
            'email_status_id' => $sentStatus->id,
        ]);

        return true;
    }

    /**
     * Send onboarding email
     *
     * @param \App\Email $email
     * @return bool
     */
    public function sendOnboardingEmail(Email $email, $receiverEmail): bool
    {
        if (!isset($receiverEmail) && !isset($email)) {
            return false;
        }
        Mail::mailer($this->getMailer())->to($receiverEmail)->send(new OnboardingEmail($email->emailDesign));
        $sentStatus = EmailStatus::whereStatus('Sent')->firstOrFail();
        $email->update([
            'email_status_id' => $sentStatus->id,
        ]);
        return true;
    }

    /**
     * Send automation email, used by send_email automation action
     *
     * @param \App\Email $email
     * @param ProcessedContact $contact
     */
    public function sendAutomationEmail(Email $email, ProcessedContact $contact, $automationId): void
    {
        $html = optional($email->emailDesign)->html;
        $senderExists = $email->sender_id;


        if (!$html) {
            throw new RuntimeException('Email html is missing.');
        }

        if (!$senderExists) {
            throw new RuntimeException('Email sender name is missing.');
        }

        $contactEmail = $contact->email;

        $email->processedContacts()->attach($contact->id);

        EmailReport::create(['email_id' => $email->id]);

        if ($contactEmail) {
            Mail::mailer($this->getMailer())->to($contactEmail)->send(new AutomationEmail($email, $contact, $automationId));
        }
    }

    /**
     * Send test email to email address(es)
     *
     * @param Email $email
     * @param array $emailAddresses
     * @throws HttpException
     */
    public function sendStandardTestEmail(Email $email, array $emailAddresses): void
    {
        if (!$email->emailDesign) {
            abort(409, 'Email design is empty');
        }

        if (!$email->sender) {
            abort(409, 'Please provide a sender for your email');
        }

        if (!$email->subject) {
            abort(409, 'Please provide both subject for your email');
        }

        foreach ($emailAddresses as $emailAddress) {
            $bouncedAddress = $this->checkIsBouncedEmail($email->account_id, $emailAddress);

            // skip any bounced email address or unsub contact
            if ($bouncedAddress) {
                continue;
            }

            if (!$emailAddress) {
                continue;
            }

            Mail::mailer($this->getMailer())->to($emailAddress)->send(new StandardTestEmail($email));
        }
    }

    public function sendReferralNotificationEmail($processedContacts, $campaign, String $emailType): void
    {
        foreach ($processedContacts as $campaignProcessedContact) {
            $this->sendNotificationEmail($campaignProcessedContact, $campaign, $emailType);
        }
    }

    public function checkIsBouncedEmail($accountId, $email, $hasSuppressionList = false)
    {
        $isInSuppressionList = $hasSuppressionList ? EmailSuppressionList::where('email_address', $email)->exists() : false;
        $isBouncedEmail = EmailBounce::where(['account_id' => $accountId, 'email_address' => $email])->exists();
        return $isInSuppressionList || $isBouncedEmail;
    }

    public function getAllBouncedEmailAddress()
    {
        $suppressionLists = EmailSuppressionList::all()->pluck('email_address')->toArray();
        $bouncedEmails = EmailBounce::all()->pluck('email_address')->toArray();
        return array_merge($suppressionLists, $bouncedEmails);
    }
}
