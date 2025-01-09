<?php

namespace App\Mail;

use App\Account;
use App\AccountDomain;
use App\AffiliateMemberAccount;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Email as MimeEmail;

class AffiliateMemberConversionEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $member;

    /**
     * Create a new message instance.
     *
     * @param \App\Account $account
     * @param $url
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
        $account = Account::find($this->member->account_id);
        $senderEmail = $emailService->formatTransactionalSenderAddress($account->company);
        $affiliateMemberDomain = AccountDomain::ignoreAccountIdScope()
        ->where([
            'account_id' => $account->id,
            'is_affiliate_member_dashboard_domain' => 1,
            'is_verified' => 1
        ])
        ->first();
        $count = 0 ;
        $levels = $this->member?->participant?->getSublines();
        foreach($levels as $level){
            $count += count($level);
        };

        $this->withSymfonyMessage(function (MimeEmail $message) {
            $message->getHeaders()->addTextHeader('X-No-Track', Str::random(10));
        });

        return $this
            ->from($senderEmail, $account->company)
            ->markdown('emailTemplates.am.conversion', [
                'member' => $this->member,
                'name' => $this->member->first_name . ' ' . $this->member->last_name,
                'account' => $account,
                'domain'=> $affiliateMemberDomain?->domain ?? '',
                'count'=> $count ?? 1,
        ]);
    }
}
