<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class AffiliateCheckVerified
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
        $user = Auth::guard('affiliateusers')->user();

        if($user == null){
            return redirect('login');
        }

        if($user->is_verified){
            return redirect('/dashboard');
        }

        return $next($request);
    }
}
