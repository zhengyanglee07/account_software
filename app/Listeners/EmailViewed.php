<?php

namespace App\Listeners;

use App\Email;
use App\Models\EmailSentEmail;
use App\SentEmailOpened;
use App\Services\EmailReportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use jdavidbakr\MailTracker\Events\ViewEmailEvent;
use jdavidbakr\MailTracker\Model\SentEmail;

class EmailViewed
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
     * @param \jdavidbakr\MailTracker\Events\ViewEmailEvent $event
     * @return void
     */
    public function handle(ViewEmailEvent $event): void
    {
        $tracker = $event->sent_email;
        $sentEmailMessageId = $tracker->message_id;
        $sentEmail = SentEmail::where('message_id', $sentEmailMessageId)
            ->firstOrFail();

        $emailSentEmail = EmailSentEmail::firstWhere('sent_email_id', $sentEmail->id);
        if(isset($emailSentEmail)){
            $email = Email::find($emailSentEmail->email_id);
            (new EmailReportService($email))->updateTotalEmailOpened();
        }

        SentEmailOpened::create([
            'sent_email_id' => $sentEmail->id
        ]);
    }
}
