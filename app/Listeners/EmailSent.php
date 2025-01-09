<?php

namespace App\Listeners;

use App\Email;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use jdavidbakr\MailTracker\Events\EmailSentEvent;
use jdavidbakr\MailTracker\Model\SentEmail;
use App\Account;
use App\ReferralSentEmail;

class EmailSent
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle EmailSent event from mail-tracker.
     *
     * Note: on the last part, sent_email_id and account_id is attached
     * on email_sent_email pivot table for the usage in email report
     *
     * @param \jdavidbakr\MailTracker\Events\EmailSentEvent $event
     * @return void
     */
    public function handle(EmailSentEvent $event): void
    {
        $tracker = $event->sent_email;
        $sentEmailMessageId = $tracker->message_id;
        $sentEmailId = SentEmail::where('message_id', $sentEmailMessageId)
            ->firstOrFail()
            ->id;
        $referralEmailId = $tracker->getHeader('X-Referral-Email-ID') ?? null;
        if ($referralEmailId) {
            //get header of referral email
            //store in referral_sent_emails table
            // to track when the email is sent
            //will pass header X-Referral-Reward-ID if the email is about reward

            $referralSentEmail = ReferralSentEmail::create([
                'referral_email_id' => $referralEmailId,
                'processed_contact_id' => $tracker->getHeader('X-Processed-Contact-ID'),
                'sent_email_id' => $sentEmailId,
            ]);

            $referralRewardId = $tracker->getHeader('X-Referral-Reward-ID') ?? null;

            if ($referralRewardId) {
                $referralSentEmail->referral_campaign_reward_id = $referralRewardId;
                $referralSentEmail->save();
            }
        }
        $accountId = $tracker->getHeader('X-Account-ID');
        $this->increaseEmailPlanTotal($accountId);

        $emailId = $tracker->getHeader('X-Email-ID');
        $processedContactId = $tracker->getHeader('X-Processed-Contact-ID');
        $email = Email::find($emailId);

        // no need to track emails sent without the X-Email-ID header,
        // like sign up verification email.
        // Note: As of July 2020, X-Email-ID only added on StandardEmail mailable
        if (!$email) {
            return;
        }

        $sentEmail = SentEmail::where('message_id', $sentEmailMessageId)
            ->first();

        if ($processedContactId) {
            $sentEmail->processed_contact_id = $processedContactId;
            $sentEmail->save();
            $email->processedContacts()->updateExistingPivot($processedContactId, ['status' => 'sent']);
        }

        $sentEmailId = $sentEmail->id;

        $email->sentEmails()->attach(
            $sentEmailId,
            ['account_id' => $email->account_id]
        );
    }

    /**
     * just increase back sending limit for ALL emails, by your boss
     * FAQ
     *
     * @param $accountId
     */
    private function increaseEmailPlanTotal($accountId): void
    {
        $accountPlanTotal = optional(Account::find($accountId))->accountPlanTotal;

        if ($accountPlanTotal) {
            ++$accountPlanTotal->total_email;
            $accountPlanTotal->save();
        }
    }
}
