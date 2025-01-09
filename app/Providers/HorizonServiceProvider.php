<?php

namespace App\Providers;

use App\Notifications\JobFailedNotification;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Notification;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Queue::failing(function (JobFailed $event) {
            Notification::route('slack',config('queue.notifications.slack.webhook'))->notify(new JobFailedNotification($event));
        });

        Horizon::routeSlackNotificationsTo(config('queue.notifications.slack.webhook'), config('queue.notifications.slack.channel'));
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewHorizon', function ($user) {
            return in_array($user->email, [
                'khawsora@gmail.com',
                'zhengyangz1007@gmail.com',
                'tzyong990808@gmail.com',
                'steve@rocketlaunch.my',
            ]);
        });
    }
}
