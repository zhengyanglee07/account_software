<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Account;
use App\Services\EmailService;
use App\ProcessedContact;
use App\User;
use App\AffiliateUser;
use Symfony\Component\Mime\Email as MimeEmail;

class ReferralNotificationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $emailData;
    public $receiver;
    public $domain;
    public $sharedPageDomain;
    public $referralCode;
    public $receiverName;
    public $account;
    public $rewardId;
    public $funnelId;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($emailData, ProcessedContact $receiver, $campaign)
    {
        $this->emailData = $emailData;
        $this->receiver = $receiver;
        $this->domain = $emailData->campaignDomain;
        $this->sharedPageDomain = $emailData->sharedPageDomain;
        $this->referralCode = $emailData->referralCode;
        $this->receiverName = $receiver->fname;
        $this->account = Account::find($campaign->account_id);
        $this->rewardId = $campaign->rewardId ?? null;
        $this->funnelId = $campaign->funnel_id ?? null;

        info($campaign);
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

        $this->emailData->template = $this->removeExtraTags();
        $this->emailData->subject = $this->replaceMergeTags($this->emailData->subject);
        $this->emailData->template = $this->replaceMergeTags($this->emailData->template);

        $user = User::find($this->account->subscription->user_id);
        $affiliate = AffiliateUser::where('email',$user->email)->first();

        $this->withSymfonyMessage(function (MimeEmail $message) {
            $message->getHeaders()->addTextHeader('X-Referral-Email-ID', $this->emailData->id);
            $message->getHeaders()->addTextHeader('X-Processed-Contact-ID', $this->receiver->id);
            if(isset($this->rewardId) && ($this->emailData->type === 'reward-unlocked')) {
                $message->getHeaders()->addTextHeader('X-Referral-Reward-ID', $this->rewardId);
            }
        });

        return $this
            ->from($senderEmailAddress, $senderName)
            ->subject($this->emailData->subject)
            ->markdown('email.referralMarketing')
            ->with([
                'content' => $this->emailData->template,
                'sellerInfo' => $this->account,
                'affiliate' => $affiliate ?? null,
            ]);
    }

    private function removeExtraTags(){
        return preg_replace_callback(
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

                }

                return $fullMatch;
            },
            $this->emailData->template
        );
    }

    private function replaceMergeTags(String $str){
        //not sure the share page url for funnel
        $header = (app()->environment('local') ? 'http://' : 'https://');
        $url = $header.($this->emailData->campaignDomain ?? $this->domain);
        $sharedUrl = $header.($this->emailData->sharedPageDomain ?? $this->sharedPageDomain);

        return preg_replace_callback(
            '|{{.*?}}|s',
            function ($matches) use ($url, $sharedUrl) {
                $fullMatch = $matches[0];

                if (!$fullMatch) return '';

                //adjust spacing of the email
                switch($fullMatch){
                    case '{{name}}':
                        return $this->receiverName;
                    case '{{share_page_url}}': //link to see the referral info
                        $sharePageUrl = $this->funnelId ? ($url.'?is_referral='. $this->referralCode) : ($url . '/referral');
                        return '['.$sharePageUrl.']'.'('.$sharePageUrl.')';
                    case '{{referral_url}}': // link to share the referral
                        $referralUrl = $sharedUrl . '?invite=' . $this->referralCode;
                        return '['.$referralUrl.']' . '(' . $referralUrl . ')';
                }

                return $fullMatch;
            },
            $str
        );
    }

}
