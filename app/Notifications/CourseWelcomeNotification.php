<?php

namespace App\Notifications;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseWelcomeNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(protected $seller, protected $courseStudent)
    {
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
        $seller = $this->seller;
        $courseStudent = $this->courseStudent;
        $courseName = $courseStudent->products?->title;
        $studentEmail = $notifiable->email;
        $studentName = $notifiable->displayName;

        $sellerEmail = $seller->email;
        $sellerName = $seller->displayName;
        $account = $seller->accounts()->first();
        $companyLogo = $account->company_logo;


        $domain = $account->domains()
            ->where(function ($query) {
                $query->where(['is_verified' => 1, 'type' => 'online-store', 'is_subdomain' => 1])
                    ->orWhere(['is_verified' => 1, 'type' => 'online-store', 'is_subdomain' => 0]);
            })
            ->first();

        $urlPrefix = (app()->environment('local') ? 'http://' : 'https://') . $domain?->domain;

        return (new MailMessage)
            ->subject("Get Ready to Learn! Welcome to $courseName")
            ->view('email.course_welcome', [
                'courseName' => $courseName,
                'studentEmail' => $studentEmail,
                'studentName' => $studentName,
                'companyLogo' => $companyLogo,
                'sellerEmail' => $sellerEmail,
                'sellerName' => $sellerName,
                'urlPrefix' => $urlPrefix,
            ]);
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
}
