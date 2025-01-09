<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckOnBoardingFirstName
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
        $user = Auth::user();
        $currentAccount = $user->accounts()->find($user->currentAccountId);

        if ($user->email_verified_at == "") {
            return redirect('/confirm/email');
        }

        if (
            $currentAccount->currency != "" && $currentAccount->company != ""
        ) {
            return redirect('/dashboard');
        }

        return $next($request);
    }
}
