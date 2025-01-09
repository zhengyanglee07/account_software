<?php

namespace App\Mail;

use App\Email;
use App\ProcessedContact;
use App\Services\EmailService;
use App\Services\MjmlComponentService;
use App\Services\MjmlRendererService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email as MimeEmail;

class AutomationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $email;
    public $contact;
    public $automationId;

    /**
     * Create a new message instance.
     *
     * @param \App\Email $email
     * @param \App\ProcessedContact $contact
     */
    public function __construct(Email $email, ProcessedContact $contact, $automationId)
    {
        $this->email = $email;
        $this->contact = $contact;
        $this->automationId = $automationId;
    }

    /**
     * Build the message.
     *
     * NOTE (as of Sept 2020):
     * this is basically the same with StandardEmail, using
     * different file just in case automation email will be vastly
     * different with StandardEmail in the future.
     *
     * If the future you who seeing this think that it's ok to
     * combine Standard & Automation email, then proceed.
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
        $senderAddress = $this->email->sender->email_address;
        $senderName = $this->email->sender_name;

        // add X-Email-ID for retrieval in EmailSent listener
        $this->withSymfonyMessage(function (MimeEmail $message) {
            $message->getHeaders()->addTextHeader('X-Email-ID', $this->email->id);
            $message->getHeaders()->addTextHeader('X-Account-ID', $this->email->account_id);
            $message->getHeaders()->addTextHeader('X-Processed-Contact-ID', $this->contact->id);

            // track bounced email with SNS
            $message->getHeaders()->addTextHeader('X-SES-CONFIGURATION-SET', 'email-bounce-and-complaint');
        });

        // affiliate badge injection
        // $html = $mjmlRendererService->render(
        //     $mjmlComponentService->parse(
        //         $emailService->injectAffiliateBadge($this->email),
        //         $this->contact->email,
        //         $this->email->account_id
        //     )
        // );

        $html = $emailService->injectAffiliateBadge($this->email);

        // merge tags (sender name && email subject)
        $senderName = $emailService->mergeTags($this->email, $this->contact, $senderName);
        $subject = $emailService->mergeTags($this->email, $this->contact, $this->email->subject);

        // merge tags (in footer)
        $html = $emailService->mergeTags($this->email, $this->contact, $html);

        return $this
            ->from($senderAddress, $senderName)
            ->subject($subject)
            ->view('email.preview')
            ->with(compact('html'));
    }
}
