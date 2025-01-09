<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Auth;
use App\Account;
use App\Http\Controllers\AccountController;



class CheckOnBoarding
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
        // create new account if user has 0 accounts currently
        // precautionary step to prevent user enter dashboard
        // without any accounts
        app(AccountController::class)->afterVerifyCreateShop();
        app(AccountController::class)->checkGeneralSetting();
        $user = Auth::user();
        $currentAccount = Account::find($user->currentAccountId);

        if ($user->email_verified_at == "") {
            return redirect('/confirm/email');
        }
        // if($currentAccount->subscription_plan_id === null || $currentAccount->subscription_status === 'cancelled'){
        //     return redirect('/subscription/plan/create');
        // }

        if (!$currentAccount->company) {
            return redirect('/onboarding/company');
        }
        
        if ($currentAccount->was_selected_goal) {
            return redirect('/onboarding/mini-store/setup');
        }

        return $next($request);
    }
}
