<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Crypt;

/**
 * Class HyperSenderDomainVerificationEmail
 * @package App\Mail
 *
 * AWS SES has another sender domain verification email, this
 * email is used to verify sender domain that is VERIFIED
 * ONCE on AWS SES.
 *
 */
class HyperSenderDomainVerificationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $accountId;
    public $email;

    /**
     * Create a new message instance.
     *
     * @param $accountId
     * @param string $email
     */
    public function __construct($accountId, string $email)
    {
        $this->accountId = $accountId;
        $this->email = $email;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): self
    {
        $url = URL::temporarySignedRoute(
            'sender.verify',
            now()->addHour(),
            [
                'account' => $this->accountId,
                'crypt' => Crypt::encryptString($this->email)
            ]
        );

        return $this
            ->subject('Please confirm your email address')
            ->with(compact('url'))
            ->markdown('emailTemplates.senderDomain.verification');
    }
}
