<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Account;
use App\User;
use App\AffiliateUser;
use App\Services\EmailService;
use Auth;

class ReferralTestEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $emailData;
    public $account;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($emailData, $accountId)
    {
        $this->emailData = $emailData;
        $this->account = Account::find($accountId);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(EmailService $emailService)
    {
        $senderName = $this->account->company;
        $senderEmailAddress = $emailService->formatTransactionalSenderAddress($senderName);

        $this->emailData['template'] = preg_replace_callback(
            '|<.*?>|s',
            function ($matches) {
                $fullMatch = $matches[0];

                if (!$fullMatch) return '';

                //adjust spacing of the email
                switch($fullMatch){
                    case '<p>':
                    case '</p>':
                        return '';
                    case '<br>':
                        return '<br><br>';
                    default:
                        return $fullMatch;

                }

                return '';
            },
            $this->emailData['template']
        );

        $user = User::find($this->account->subscription->user_id);
        $affiliate = AffiliateUser::where('email',$user->email)->first();

        return $this
            ->from($senderEmailAddress, $senderName)
            ->subject($this->emailData['subject'])
            ->markdown('email.referralMarketing')
            ->with([
                'content' => $this->emailData['template'],
                'sellerInfo' => $this->account,
                'affiliate' => $affiliate
            ]);
    }
}
