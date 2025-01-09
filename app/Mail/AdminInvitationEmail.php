<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Crypt;
use App\Services\EmailService;
use App\RoleInvitationEmail;
use App\Account;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Email as MimeEmail;

class AdminInvitationEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(RoleInvitationEmail $invite, Account $account)
    {
        $this->invite = $invite;
        $this->account = $account;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(EmailService $emailService)
    {
        $invite = $this->invite;
        $account = $this->account;
        $senderEmail = $emailService->formatTransactionalSenderAddress($account->company);
        $url = URL::temporarySignedRoute(
            'accept-invitation',
            now()->addHour(),
            [
                'token' => $this->invite->token,
                'randomId' => $this->invite->account_random_id,
                'encryptedEmail' => Crypt::encryptString($this->invite->email)
            ]
        );

        // disable email tracking on this email
        $this->withSymfonyMessage(function (MimeEmail $message) {
            $message->getHeaders()->addTextHeader('X-No-Track', Str::random(10));
        });

        // $url = "";
        return $this
            ->from($senderEmail, $account->company)
            ->markdown('email.adminInvitation')->with(compact('url', 'invite', 'account'));
    }
}
