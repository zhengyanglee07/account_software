<?php

namespace App\Listeners;

use App\Email;
use App\Models\EmailSentEmail;
use App\SentEmailOpened;
use App\Services\EmailReportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use jdavidbakr\MailTracker\Model\SentEmail;

class EmailLinkClicked
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
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $tracker = $event->sent_email;
        $sentEmailMessageId = $tracker->message_id;
        $sentEmail = SentEmail::where('message_id', $sentEmailMessageId)
            ->firstOrFail();
            
        //to record the first time click as open also if open was 0
        if ($event->sent_email->opens === 0) {
            $sentEmail->opens++;
            $sentEmail->save();
            SentEmailOpened::create([
                'sent_email_id' => $sentEmail->id
            ]);
        }

        $emailSentEmail = EmailSentEmail::firstWhere('sent_email_id', $sentEmail->id);
        if(isset($emailSentEmail)){
            $email = Email::find($emailSentEmail->email_id);
            (new EmailReportService($email))->updateTotalEmailClicked();
        }
    }
}
