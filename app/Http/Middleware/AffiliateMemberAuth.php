<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class AffiliateMemberAuth
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
        $user = Auth::guard('affiliateMember')->user();
        
        if(!$user) return redirect('/affiliates/login');
        
        return $next($request);
    }
}
