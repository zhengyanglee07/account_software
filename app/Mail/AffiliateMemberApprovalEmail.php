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

class AffiliateMemberApprovalEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $member;
    protected $url;

    /**
     * Create a new message instance.
     *
     * @param \App\AffiliateMemberAcoount $member
     */
    public function __construct(AffiliateMemberAccount $member)
    {
        $this->member = $member;
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
        $affiliateName = $this->member->first_name . ' '. $this->member->last_name;
        \Log::debug('domain', [
            'd' => config('app.domain'),
        ]);
        return $this
            ->from('mail@myhypershapes.com', 'Hypershapes')
            ->subject('New affiliate is waiting your approval')
            ->markdown('emailTemplates.am.approval', [
                'account' => $account,
                'name' => $name,
                'affiliateName' => $affiliateName,
                'domain'=>config('app.domain'),
            ]);
    }
}
