<?php

namespace App\Mail;

use App\Account;
use App\AffiliateUser;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email as MimeEmail;

class LandingPageFormSubmissionEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $emailSettings;
    public $formFields;
    public $accountId;

    /**
     * Create a new message instance.
     *
     * @param array $emailSettings
     * @param array $formFields
     * @param $accountId
     */
    public function __construct(array $emailSettings, array $formFields, $accountId)
    {
        $this->emailSettings = $emailSettings;
        $this->formFields = $formFields;
        $this->accountId = $accountId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): self
    {
        $subject = $this->emailSettings['subject'];
        $senderEmail = $this->emailSettings['senderEmail'];
        $senderName = $this->emailSettings['senderName'];

        $account = Account::find($this->accountId);
        $user = User::find($account->subscription->user_id);
        $affiliate = AffiliateUser::where('email',$user->email)->first();
		$hasAffiliateBadge = $account->has_email_affiliate_badge ?? true;

        // used to increase email plan total in EmailSent
        $this->withSymfonyMessage(function (MimeEmail $message) {
            $message->getHeaders()->addTextHeader('X-Account-ID', $this->accountId);
        });

        return $this
            ->from($senderEmail, $senderName)
            ->subject($subject)
            ->view('emailTemplates.landingPageForm.submission')
            ->with([
                'formFields' => $this->formFields,
                'affiliate' => $affiliate,
				'hasAffiliateBadge' => $hasAffiliateBadge
            ]);
    }
}
