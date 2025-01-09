<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Events\JobFailed;


class JobFailedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(protected JobFailed $event)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack($notifiable)
    {
        $job = $this->event->job;
        return (new SlackMessage)
                    ->from('Laravel Horizon')
                    ->to(config('queue.notifications.slack.channel'))
                    ->error()
                    ->content('Oh no! Something needs your attention.')
                    ->attachment(function ($attachment) use ($job) {
                        $failedJobUrl = config('app.url') .'/horizon/failed/'. $job->uuid();
                        $attachment->title('Failed Job Detected')
                                   ->content(sprintf(
                                        '[%s] The "%s" job in "%s" queue on the "%s" connection (%s)',
                                       config('app.name'),$job->resolveName() ,$job->getQueue(), $job->getConnectionName(), $failedJobUrl
                                   ));
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
}
