<?php

namespace App\Http\Middleware;

use App\Account;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckOnBoardingEmail
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
        $user=Auth::user();

        if($user->email_verified_at === "" ){
            return redirect('/confirm/email');
        }

        if ($user->lastName != "") {
            return redirect('/dashboard');
        }

        return $next($request);
    }
}
