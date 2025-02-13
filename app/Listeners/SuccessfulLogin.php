<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Account;
class SuccessfulLogin
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
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        $event->user->last_login_at = date('Y-m-d H:i:s');
        if ($event->guard === 'web') {
            if ($event->user->currentAccount() != null) {
                $event->user->currentAccountId = $event->user->ownAccountId();
            }
            $event->user->save();
        }
    }
}
