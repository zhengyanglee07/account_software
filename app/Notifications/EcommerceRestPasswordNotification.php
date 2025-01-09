<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use App\ProcessedContact;
use App\Account;
use App\AccountUser;
use App\User;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Email as MimeEmail;

class EcommerceRestPasswordNotification extends Notification
{
    use Queueable;

    public static $toMailCallback;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $customerInfo = ProcessedContact::find($notifiable->processed_contact_id);
        $sellerInfo = Account::find($notifiable->account_id);

        $senderAddress = $this->formatTransactionalSenderAddress($sellerInfo->company);

        $urlHeader = $_SERVER['HTTP_HOST'] === 'hypershapes.test' ? 'http://' : 'https://';
        $email = urlencode($notifiable->email);
        $url = $urlHeader . $_SERVER['HTTP_HOST'] . '/customer-account/password/reset/' . $this->token . '?email=' . $email;

        if (!empty($_SERVER['HTTP_ORIGIN'])) {
            $url = $_SERVER['HTTP_ORIGIN'] . '/customer-account/password/reset/' . $this->token . '?email=' . $email;
        }

        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        // return (new MailMessage)
        // ->subject(Lang::get('Reset Password Notification'))
        // ->line(Lang::get('You are receiving this email because we received a password reset request for your account.'))
        // ->action(Lang::get('Reset Password'), url(route('affiliate.password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()], false)))
        // ->line(Lang::get('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
        // ->line(Lang::get('If you did not request a password reset, no further action is required.'))
        // ->from($sellerInfo);

        // $this->withSwiftMessage(function ($message) {
        //     $message->getHeaders()->addTextHeader('X-No-Track', Str::random(10));
        // });

        return (new MailMessage)
            ->from($senderAddress, $sellerInfo->company ?: 'Hypershapes')
            ->subject('Reset Password Notification')
            ->markdown(
                'email.ecommerce.resetPassword',
                [
                    'sellerInfo' => $sellerInfo,
                    'ecommerceAccount' => $customerInfo,
                    'url' => $url,
                ]
            )
            ->withSymfonyMessage(function (MimeEmail $message) {
                $message->getHeaders()->addTextHeader('X-No-Track', Str::random(10));
            });
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    public function formatTransactionalSenderAddress($senderName): string
    {
        // use default address if company name is empty
        if (!$senderName) return 'mail@notification.hypershapes.com';

        // lower case and trim spaces
        $senderName = strtolower($senderName);
        $senderName = trim($senderName);

        // &: handle case like 'Tee & Co.'
        $senderName = str_replace([' ', '&'], ['-', 'and'], $senderName);

        // remove all chars except alpha num & -
        $senderName = preg_replace('/[^A-Za-z0-9-]/', '', $senderName);

        return "$senderName@notification.hypershapes.com";
    }
}
