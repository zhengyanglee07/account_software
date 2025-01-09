<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Subscription;
use App\SubscriptionInvoice;
use App\SubscriptionPlan;
use App\Traits\TestAccountTrait;
use DB;

class AdminReportController extends Controller
{
    use TestAccountTrait;
    private const ACTIVE_OR_TRIALLING = ['active', 'trialing'];

    /**
     *
     */
    public function subscriptionsReport()
    {
        
        $subscriptions = Subscription::whereNotIn('account_id', $this->getTestAccountIds())->get();
        $paidAndTrialingSubscriptionsCount = $subscriptions
            ->filter(function ($s) {
                return $s->status === 'trialing'
                    && $s->subscription_id
                    && $s->subscription_plan_id !== 1; // 1 is free plan
            })
            ->count();
        $subscriptionPlans = SubscriptionPlan
            ::with(['subscriptions' => function ($query) {
                $query
                    ->whereIn('status', self::ACTIVE_OR_TRIALLING)
                    ->whereNotIn('account_id', $this->getTestAccountIds());
            }])
            ->get();
        $totalRevenue = $this->calculateTotalRevenue(); // cater for stripe default multiply by 100

        $report = [
            'trialing' => $paidAndTrialingSubscriptionsCount,
            'free' => $this->countSubscriptionPlans($subscriptionPlans, 'Free'),
            'square' => $this->countSubscriptionPlans($subscriptionPlans, 'Square'),
            'triangle' => $this->countSubscriptionPlans($subscriptionPlans, 'Triangle'),
            'circle' => $this->countSubscriptionPlans($subscriptionPlans, 'Circle'),
            'totalPaidUsers' => SubscriptionInvoice::whereNotIn('account_id', $this->getTestAccountIds())->where('total','>',0)->get()->groupBy('account_id')->count(),
            'totalRevenue' => $totalRevenue
        ];

        return response()->json([
            'status' => 'success',
            'report' => $report
        ]);
    }

    /**
     * @return int|mixed
     */
    private function calculateTotalRevenue()
    {
        return  SubscriptionInvoice
        ::whereNotIn('account_id', $this->getTestAccountIds())
        ->where('total','>',0)->sum('total');
    }

    /**
     * @param $subscriptionPlans
     * @param $type
     * @return mixed
     */
    private function countSubscriptionPlans($subscriptionPlans, $type)
    {
        return $subscriptionPlans->firstWhere('plan', $type)->subscriptions->count();
    }
}
