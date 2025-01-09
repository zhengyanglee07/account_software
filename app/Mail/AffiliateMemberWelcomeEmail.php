<?php

namespace App\Mail;

use App\Account;
use App\AccountDomain;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AffiliateMemberWelcomeEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $member;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($member)
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
        return $this
            ->from($senderEmail, $account->company)
            ->markdown('emailTemplates.am.welcome', [
            'member' => $this->member,
            'name' => $this->member->first_name . ' ' . $this->member->last_name,
            'account' => $account,
            'domain'=> $affiliateMemberDomain?->domain ?? '',
        ]);
    }
}
