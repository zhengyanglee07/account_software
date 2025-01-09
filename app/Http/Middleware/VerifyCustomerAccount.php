<?php

namespace App\Http\Middleware;

use Closure;
use App\EcommercePreferences;
use App\Traits\AuthAccountTrait;
use Auth;
use App\Traits\PublishedPageTrait;

class VerifyCustomerAccount
{
    use PublishedPageTrait, AuthAccountTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        $preferences = EcommercePreferences::firstWhere('account_id', $this->getCurrentAccountId());
        $isAccountDisabled = $preferences->require_account === 'disabled';
        if($this->guard()->check() && $isAccountDisabled){
            $this->guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $verifyRoute = ['checkout/shipping','checkout/payment'];
        if(!in_array($request->path(), $verifyRoute)){
            return $next($request);
        }

        $user = $this->guard()->user();
        $preferences = EcommercePreferences::firstWhere('account_id', $this->getCurrentAccountId());
        if ($preferences->require_account === 'required') {
            if ($user === null) {
                setcookie('errorMessage', 'Required Customer Login', time() + (86400 * 30), "/");
                return redirect('/checkout/information');
            }
        }
        return $next($request);
    }

    protected function guard()
    {
        return Auth::guard('ecommerceUsers');
    }
}
