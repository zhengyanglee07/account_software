<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Account;
use App\AccountPlanTotal;
use App\Http\Controllers\AccountController;
use App\Subscription;
use App\SubscriptionPlan;

class CheckOnBoardingApi
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
        $user = Auth::user();
        $currentAccount = Account::find($user->currentAccountId);
        $currentSubscription = Subscription::where("account_id",$user->currentAccountId)->first();

        if ($user->email_verified_at == "") {
            return redirect('/confirm/email');
        }
        // if($currentAccount->subscription_plan_id === null || $currentAccount->subscription_status === 'cancelled'){
            //     return redirect('/subscription/plan/create');
            // }

        if (!$currentAccount->company) {
            return redirect('/onboarding/company');
        }

        if (!(boolean)$currentAccount->was_selected_goal) {
            return redirect('/onboarding/salechannels');
        }

        if (!(boolean)$currentAccount->is_onboarded) {
            return redirect('/onboarding/mini-store/setup');
        }
        app(AccountController::class)->checkGeneralSetting();

        return $next($request);
    }
}
