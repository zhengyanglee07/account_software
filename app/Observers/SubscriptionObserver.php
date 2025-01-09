<?php

namespace App\Observers;

use App\Services\BossAcctOpsService;
use App\Subscription;
use App\AffiliateReferralLog;
use App\AffiliateDetail;

class SubscriptionObserver
{
    private $bossAcctOpsService;

    public function __construct(BossAcctOpsService $bossAcctOpsService)
    {
        $this->bossAcctOpsService = $bossAcctOpsService;
    }

    /**
     * Handle the subscription "created" event.
     *
     * @param  \App\Subscription  $subscription
     * @return void
     */
    public function created(Subscription $subscription)
    {
		// dd($subscription);
        $affiliateReferral = AffiliateReferralLog::where('user_id',$subscription->user_id)->first();

		if($affiliateReferral !== null){
			// $affiliateReferral=AffiliateReferralLog::where('user_id',$subscription->user_id)->first();
			$affiliateReferral->subscription_id = $subscription->id;
			$affiliateReferral->referral_status = $subscription->status;
            $affiliateReferral->save();

			$affiliateDetail = AffiliateDetail::where('affiliate_userid',$affiliateReferral->affiliate_id)->first();

			if($subscription->status == 'trialing'){
				$affiliateDetail->current_trial_account += 1;
				$affiliateDetail->save();

			}else if($subscription->status == 'active' && $subscription->subscription_plan_id > 1){
				$affiliateDetail->current_paid_account += 1;
                $affiliateDetail->save();

			}else if($subscription->status == 'active' && $subscription->subscription_plan_id == 1){
                $affiliateDetail->current_free_user +=1;
                $affiliateReferral->isFreePlan = true;
                $affiliateDetail->save();
                $affiliateReferral->save();
            }
		}

        // update steve's account
        $this->bossAcctOpsService->addOrUpdateSubscriptionTagInContact($subscription);
    }

    /**
     * Handle the subscription "updated" event.
     *
     * @param  \App\Subscription  $subscription
     * @return void
     */
    public function updated(Subscription $subscription)
    {
        $affiliateReferral = AffiliateReferralLog::where('user_id', $subscription->user_id)->first();

		if($affiliateReferral !== null){
            $allReferral = AffiliateReferralLog::where('affiliate_id',$affiliateReferral->affiliate_id)
            ->where('subscription_id','!=',null)->where('user_id','!=',NULL)->where('parent_id','!=',NULL)->get();
            $allSubscription = [];
                foreach($allReferral as $referral){
                    $referralSubscription = $referral->subscription;
                    array_push($allSubscription,$referralSubscription);
                }
                if(count($allSubscription) > 0){
                    $filteredFreePlan = array_filter($allSubscription, function($obj){
                        return $obj->subscription_plan_id == 1;
                    });
                    $filteredPaidPlan = array_filter($allSubscription,function($obj){
                        return $obj->subscription_plan_id >1 && $obj->status == 'active';
                    });
                    $filteredTrialPlan = array_filter($allSubscription,function($obj){
                        return $obj->subscription_plan_id >1 && $obj->status == 'trialing';
                    });
                    //save updated status to referral log
                    $affiliateReferral->referral_status = $subscription->status;
                    $affiliateReferral->save();
                    //update count of total current plan user
                    $affiliateDetail = AffiliateDetail::where('affiliate_userid',$affiliateReferral->affiliate_id)->first();
                    $affiliateDetail->current_free_user = count($filteredFreePlan);
                    $affiliateDetail->current_paid_account = count($filteredPaidPlan);
                    $affiliateDetail->current_trial_account = count($filteredTrialPlan);
                    $affiliateDetail->save();
                }
	    }

        // update steve's account
        $this->bossAcctOpsService->addOrUpdateSubscriptionTagInContact($subscription);
    }

    /**
     * Handle the subscription "deleted" event.
     *
     * @param  \App\Subscription  $subscription
     * @return void
     */
    public function deleted(Subscription $subscription)
    {
        //
    }

    /**
     * Handle the subscription "restored" event.
     *
     * @param  \App\Subscription  $subscription
     * @return void
     */
    public function restored(Subscription $subscription)
    {
        //
    }

    /**
     * Handle the subscription "force deleted" event.
     *
     * @param  \App\Subscription  $subscription
     * @return void
     */
    public function forceDeleted(Subscription $subscription)
    {
        //
    }
}
