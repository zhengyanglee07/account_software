<?php

namespace App\Providers;

use App\Listeners\AutomationTriggerEventSubscriber;
use App\Observers\OrderObserver;
use App\Observers\UserObserver;
use App\Order;
use App\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use jdavidbakr\MailTracker\Events\EmailSentEvent;
use App\Listeners\EmailSent;
use jdavidbakr\MailTracker\Events\ViewEmailEvent;
use App\Listeners\EmailViewed;
use jdavidbakr\MailTracker\Events\LinkClickedEvent;
use App\Listeners\EmailLinkClicked;
use jdavidbakr\MailTracker\Events\EmailDeliveredEvent;
use App\Listeners\EmailDelivered;
use App\Observers\SubscriptionObserver;
use App\Observers\SubscriptionLogObserver;
use App\Subscription;
use App\SubscriptionLogs;
use App\Listeners\SuccessfulLogin;


class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        EmailSentEvent::class => [
            EmailSent::class,
        ],
        ViewEmailEvent::class => [
            EmailViewed::class,
        ],
        LinkClickedEvent::class => [
            EmailLinkClicked::class,
        ],
        EmailDeliveredEvent::class => [
            EmailDelivered::class,
        ],

        Login::class => [
            SuccessfulLogin::class
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        AutomationTriggerEventSubscriber::class
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Subscription::observe(SubscriptionObserver::class);
        SubscriptionLogs::observe(SubscriptionLogObserver::class);
        User::observe(UserObserver::class);
        Order::observe(OrderObserver::class);
    }
}
