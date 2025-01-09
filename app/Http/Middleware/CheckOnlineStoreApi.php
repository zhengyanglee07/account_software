<?php

namespace App\Http\Middleware;

use App\Traits\AuthAccountTrait;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckOnlineStoreApi
{
    use AuthAccountTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $ecommerceAccount = Auth::guard('ecommerceUsers');
        // dd([!$ecommerceAccount->check(), !$ecommerceAccount->user()->is_verify]);
        if (!$ecommerceAccount->check()) return abort(302, '/customer-account/login');

        if (!$ecommerceAccount->user()->is_verify) return abort(302, '/customer-account/email/verification');

        return $next($request);
    }
}
