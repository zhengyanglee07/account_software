<?php

namespace App\Listeners;

use App\EmailBounce;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use jdavidbakr\MailTracker\Events\PermanentBouncedMessageEvent;

/**
 * @deprecated
 *
 * To be removed
 *
 * Class BouncedEmail
 * @package App\Listeners
 */
class BouncedEmail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * Simply add bounced email address obtained from SNS to email_bounces db
     *
     * @param \jdavidbakr\MailTracker\Events\PermanentBouncedMessageEvent $event
     * @return void
     */
    public function handle(PermanentBouncedMessageEvent $event): void
    {
        EmailBounce::firstOrCreate([
            'email_address' => $event->email_address
        ]);
    }
}
