<?php

namespace App\Mail;

use App\Account;
use App\AffiliateMemberAccount;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Email as MimeEmail;

class AffiliateMemberSignupEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $url;

    /**
     * Create a new message instance.
     *
     * @param \App\AffiliateMemberAccount $user
     * @param $url
     */
    public function __construct(AffiliateMemberAccount $user, $url)
    {
        $this->user = $user;
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @param \App\Services\EmailService $emailService
     * @return $this
     */
    public function build(EmailService $emailService)
    {
        $account = Account::find($this->user->account_id);
        $senderName = $account->store_name ?? $account->company;
        $senderAddress = $emailService->formatTransactionalSenderAddress($senderName);

        // disable email tracking on this email
        $this->withSymfonyMessage(function (MimeEmail $message) {
            $message->getHeaders()->addTextHeader('X-No-Track', Str::random(10));
        });

        return $this
            ->from($senderAddress, $senderName ?: 'Hypershapes')
            ->markdown('affiliate.member.signupEmail', [
            'user' => $this->user,
            'url' => $this->url,
            'account' => $account
        ]);
    }
}
