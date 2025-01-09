<?php

namespace App\Observers;

use App\Account;
use App\SubscriptionLogs;
use App\Subscription;
use App\AffiliateDetail;
use App\AffiliateCommissionLog;
use App\AffiliateReferralLog;
use App\AffiliateUser;
use App\AffiliateUserReferral;
use Carbon\Carbon;

class SubscriptionLogObserver
{
    /**
     * Handle the subscription logs "created" event.
     *
     * @param  \App\SubscriptionLogs  $subscriptionLogs
     * @return void
     */

    public function created(SubscriptionLogs $subscriptionLogs)
    {
        $subscription = Subscription::where('subscription_id',$subscriptionLogs->subscription_id)->first();
        if($subscription){
            $affiliateReferral = AffiliateReferralLog::where('user_id',$subscription->user_id)->first();
            if($affiliateReferral && (int)$subscriptionLogs->amount_paid !== 0 ){
                if($affiliateReferral->parent){
                    $affiliateAncestors = AffiliateReferralLog::defaultOrder('desc')->ancestorsAndSelf($affiliateReferral->id);

                    if($affiliateReferral->subscription_lock_status === null){
                        $affiliateReferral->subscription_lock_status = 'paid';
                        $affiliateReferral->save();
                    }

                    $this->tierLockStatus($affiliateReferral);

                    foreach($affiliateAncestors as $key=> $affiliate){
                        if($key > 1){
                            break;
                        }

                        // if($affiliate->parent && $affiliate->subscription_lock_status =='paid' && $key == 1){
                        if($key == 1){
                            $this->tierLockStatus($affiliate);
                            if($affiliate->tier_lock_status!=='standard'){
                                $commissionRate = $affiliate->tier_lock_status === 'expert' ? 0.1 : 0.05;
                                $affiliateCommission = new AffiliateCommissionLog();
                                $affiliateCommission->referral_id = $affiliateReferral->id;
                                $affiliateCommission->affiliate_id = $affiliate->affiliate_id;
                                $affiliateCommission->subscription_log_id = $subscriptionLogs->id;
                                $affiliateCommission->commission = (floatVal($subscriptionLogs->amount_paid)*$commissionRate)/100;
                                $affiliateCommission->paid_date = now();
                                // $affiliateCommission->credited_date = Carbon::now()->addMinutes(2);
                                $affiliateCommission->credited_date = Carbon::now()->addMonths(2);
                                $affiliateCommission->commission_rate = $commissionRate;
                                $affiliateCommission->save();
                            }
                        }

                        if($key == 0){
                            $commissionRate = $this->levelOneComission($affiliateReferral->tier_lock_status);
                            $affiliateCommission = new AffiliateCommissionLog();
                            $affiliateCommission->referral_id = $affiliateReferral->id;
                            $affiliateCommission->affiliate_id = $affiliate->affiliate_id;
                            $affiliateCommission->subscription_log_id = $subscriptionLogs->id;
                            $affiliateCommission->commission = (floatVal($subscriptionLogs->amount_paid)*$commissionRate)/100;
                            $affiliateCommission->paid_date = now();
                            $affiliateCommission->credited_date = Carbon::now()->addMonths(2);
                            $affiliateCommission->commission_rate = $commissionRate;
                            $affiliateCommission->save();
                        }

                        $affiliateDetail = $affiliate->referFromAffiliate->affiliateDetail;
                        $affiliateDetail->total_pending_commission = AffiliateCommissionLog::where('affiliate_id',$affiliate->affiliate_id)->where('commission_status','pending')->sum('commission');
                        $affiliateDetail->total_commission = AffiliateCommissionLog::where('affiliate_id',$affiliate->affiliate_id)->sum('commission');
                        $affiliateDetail->save();

                    }
                }
            }
        }

    }

    public static function levelOneComission($tier){
        switch ($tier) {
            case 'expert':
                return 0.4;
            case 'pro':
                return 0.3;
            default:
               return 0.2;
        }
    }

    public static function tierLockStatus($affiliateReferral){
        if($affiliateReferral->tier_lock_status === null){
            $affiliateReferral->tier_lock_status = 'standard';
            $referrals = AffiliateReferralLog::where(['refer_from_affiliate_id'=>$affiliateReferral->refer_from_affiliate_id, 'subscription_lock_status' => 'paid'])->get();
            if(count($referrals)>(app()->environment('staging') ? 1 : 10))  $affiliateReferral->tier_lock_status = 'pro';
            if(count($referrals)>(app()->environment('staging') ? 5 : 50))  $affiliateReferral->tier_lock_status = 'expert';
            $affiliateReferral->save();
        }
    }


    /**
     * Handle the subscription logs "updated" event.
     *
     * @param  \App\SubscriptionLogs  $subscriptionLogs
     * @return void
     */
    public function updated(SubscriptionLogs $subscriptionLogs)
    {
        //
    }

    /**
     * Handle the subscription logs "deleted" event.
     *
     * @param  \App\SubscriptionLogs  $subscriptionLogs
     * @return void
     */
    public function deleted(SubscriptionLogs $subscriptionLogs)
    {
        //
    }

    /**
     * Handle the subscription logs "restored" event.
     *
     * @param  \App\SubscriptionLogs  $subscriptionLogs
     * @return void
     */
    public function restored(SubscriptionLogs $subscriptionLogs)
    {
        //
    }

    /**
     * Handle the subscription logs "force deleted" event.
     *
     * @param  \App\SubscriptionLogs  $subscriptionLogs
     * @return void
     */
    public function forceDeleted(SubscriptionLogs $subscriptionLogs)
    {
        //
    }
}
