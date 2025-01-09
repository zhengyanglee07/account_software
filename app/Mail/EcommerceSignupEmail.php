<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use App\Services\EmailService;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Email as MimeEmail;

class EcommerceSignupEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $email_data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->email_data = $data;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(EmailService $emailService)
    {
        $sellerInfo = $this->email_data['sellerInfo'];
        $senderName = $sellerInfo->company;
        $senderAddress = $emailService->formatTransactionalSenderAddress($senderName);

        // disable email tracking on this email
        $this->withSymfonyMessage(function (MimeEmail $message) {
            $message->getHeaders()->addTextHeader('X-No-Track', Str::random(10));
        });

        return $this->from($senderAddress, $senderName ?: 'Hypershapes')
        ->markdown('email.ecommerce.signup-email')
        ->with($this->email_data);
    }
}
