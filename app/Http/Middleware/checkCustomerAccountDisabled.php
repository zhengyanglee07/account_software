<?php

namespace App\Http\Middleware;

use Closure;
use App\Traits\AuthAccountTrait;
use App\EcommercePreferences;

class checkCustomerAccountDisabled
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
        $ecommercePreference = EcommercePreferences::firstWhere(['account_id' => $this->getCurrentAccountId()]);

        if($ecommercePreference->require_account === 'disabled') {
            abort(404, 'Customer account is disabled.');
        }
        return $next($request);
    }
}
