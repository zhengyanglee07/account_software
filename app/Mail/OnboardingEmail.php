<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\User;
use App\ProcessedContact;
use App\Services\EmailService;
use App\Services\MjmlComponentService;
use App\Services\MjmlRendererService;

class OnboardingEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $emailDesign;
    public $html;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($emailDesign)
    {
        $this->emailDesign = $emailDesign;
        $this->html = $emailDesign['html'];
    }

    /**
     * Build the message.
     *
     * @param \App\Services\EmailService $emailService
     * @param \App\Services\MjmlRendererService $mjmlRendererService
     * @param \App\Services\MjmlComponentService $mjmlComponentService
     * @return $this
     */
    public function build(
        EmailService $emailService,
        MjmlRendererService $mjmlRendererService,
        MjmlComponentService $mjmlComponentService
    ): self {
        $senderEmail = app()->environment() == 'local' ? 'steve@gmail.com' : 'steve@rocketlaunch.my';
        $sender = User::firstWhere('email', $senderEmail);
        $senderProcessedContact = ProcessedContact::firstWhere('email', $senderEmail);
        $senderName = $sender->firstName . ' ' . $sender->lastName;
        $senderEmailAddress = $sender->email;

        // $senderContacts = $this->sender['contact'];

        // add X-Email-ID for retrieval in EmailSent listener
        // $this->withSwiftMessage(function ($message) {
        //     $message->getHeaders()->addTextHeader('X-Email-ID', $this->email->id);
        //     $message->getHeaders()->addTextHeader('X-Account-ID', $this->email->account_id);

        //     // track bounced email with SNS
        //     $message->getHeaders()->addTextHeader('X-SES-CONFIGURATION-SET', 'email-bounce-and-complaint');
        // });


        $this->html = $mjmlRendererService->render(
            $mjmlComponentService->parse(
                $this->emailDesign['mjml']
            )
        );

        // merge tags (in footer)
        $this->html = $emailService->mergeTags($this->emailDesign->email, $senderProcessedContact, $this->html);

        return $this
            ->from($senderEmailAddress, $senderName)
            ->subject($this->emailDesign->email['subject'])
            ->view('email.preview');
    }
}
