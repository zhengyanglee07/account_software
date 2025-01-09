<?php

namespace App\Mail;

use App\Account;
use App\AffiliateMemberAccount;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Email as MimeEmail;

class AffiliateMemberRequestPayoutEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $member;
    protected $url;

    /**
     * Create a new message instance.
     *
     * @param \App\AffiliateMemberAcoount $member
     */
    public function __construct(AffiliateMemberAccount $member, $payout)
    {
        $this->member = $member;
        $this->status = $payout['status'];
        $this->amount = $payout['amount'];
        $this->currency = $payout['currency'];
    }

    /**
     * Build the message.
     *
     * @param \App\Services\EmailService $emailService
     * @return $this
     */
    public function build(EmailService $emailService)
    {
        $senderAddress = $emailService->formatTransactionalSenderAddress(null);
        $account = Account::find($this->member->account_id);
        $this->withSymfonyMessage(function (MimeEmail $message) {
            $message->getHeaders()->addTextHeader('X-No-Track', Str::random(10));
        });
        $name  = $account?->user?->first()?->firstName ?? '';
        $affiliateEmail = $this->member->email;
        $affiliateName = $this->member->first_name;
        \Log::debug('domain', [
            'd' => config('app.domain'),
        ]);
        return $this
            ->from($senderAddress, 'Hypershapes')
            ->subject($affiliateName. ' request for commission payout!')
            ->markdown('emailTemplates.am.payout.request', [
                'account' => $account,
                'name' => $name,
                'affiliateEmail' => $affiliateEmail,
                'domain'=>config('app.domain'),
                'amount' => $this->currency . ' ' . ($this->amount ?? 0),
            ]);
    }
}
