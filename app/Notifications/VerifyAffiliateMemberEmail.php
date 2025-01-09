<?php

namespace App\Notifications;

use App\AccountDomain;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use App\Mail\AffiliateMemberSignupEmail;
use App\Services\AffiliateEmailService;

class VerifyAffiliateMemberEmail extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \App\Mail\AffiliateMemberSignupEmail|\Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new AffiliateMemberSignupEmail($notifiable, $this->verificationUrl($notifiable)))->to($notifiable->email);
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param mixed $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        $affiliateMemberDomain = AccountDomain
            ::where([
                'account_id' => $notifiable->account_id,
                'is_affiliate_member_dashboard_domain' => 1,
                'is_verified' => 1
            ])
            ->first();

        if (!$affiliateMemberDomain) {
            return '';
        }

        return AffiliateEmailService::getUrl('/affiliates/email/verify', [
            'domain' => $affiliateMemberDomain->domain,
            'id' => $notifiable->getKey(),
            'hash' => sha1($notifiable->getEmailForVerification()),
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
