<?php

namespace App\Http\Middleware;

use App\Account;
use App\Http\Controllers\AccountController;
use App\Subscription;
use Closure;
use Illuminate\Support\Facades\Auth;
use App\User;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        app(AccountController::class)->afterVerifyCreateShop();

        $user = Auth::user();

        if ($user->email_verified_at == "") {
            return redirect('/confirm/email');
        }
        return $next($request);
    }
}
