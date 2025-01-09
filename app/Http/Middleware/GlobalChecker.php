<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Support\Facades\Auth;
use App\Account;
use App\AccountPlanTotal;
use App\Http\Controllers\AccountController;
use App\Subscription;
use App\SubscriptionPlan;

class GlobalChecker
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
        $account = $user->currentAccount();
        $currentSubscription = Subscription::where("account_id", $user->currentAccountId)->first();

        $isSubscriptionActive = true;
        if ($account !== null) {
            if (
                $account->subscription_status === 'canceled'
                || $account->subscription_status === 'incomplete'
                || $account->subscription_status === 'incomplete_expired'
            ) {
                $isSubscriptionActive = !$isSubscriptionActive;
                return redirect('/subscription/plan/upgrade');
            }

            Account::find($user->currentAccountId)->saleChannels()->update([
                'is_active' => $isSubscriptionActive
            ]);
        }

        $uncontrollableSlug = [
            'add-people' => 'total_people',
            'send-email' => 'total_email',
            'add-form' => 'total_form',
            'add-affiliate-member' => 'total_affiliate_member',
        ];

        if ($currentSubscription !== null) {
            foreach ($uncontrollableSlug as $slug => $totalProperty) {
                $max = $account->permissionMaxValue($slug);
                $total =  $account->accountPlanTotal[$totalProperty];
                $isFreePlan = $currentSubscription->subscription_plan_id === 1;
                $isPlanAllowAffiliateMember = $currentSubscription->subscription_plan_id > 2;

                if (
                    $total > $max &&
                    ($slug !== 'add-affiliate-member' || $isPlanAllowAffiliateMember) &&
                    ($slug !== 'add-form' || $isFreePlan)
                ) {
                    return redirect('/subscription/plan/upgrade');
                }
            }
            $canDisableBadges = $currentSubscription->subscription_plan_id > 2;
        }

        view()->share('canDisableBadges', $canDisableBadges ?? false);

        return $next($request);
    }
}
