<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email as MimeEmail;

class TestingMail extends Mailable
{
    use Queueable, SerializesModels;

    public $accountId;

    /**
     * Create a new message instance.
     *
     * @param $accountId
     */
    public function __construct($accountId)
    {
        $this->accountId = $accountId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->withSymfonyMessage(function (MimeEmail $message)  {
            $message->getHeaders()->addTextHeader('X-Account-ID', $this->accountId);
        });

        return $this
            ->subject('[Hyper] Testing')
            ->html('<p>hello, this is a testing mail.</p>');
    }
}
