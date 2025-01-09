<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccountPlanTotal extends Model
{
    protected $guarded = [];


    public function autoUpgrade($accountPlanTotal){
        //stripe Api Keys
        $key =
        [
//            'local' => 'sk_test_51Hh9jlFeI4XEtTz4pjvSNvdVnpqlB7vBUJEEToglMjdT0gskUzZu9Hejek1gzTWYIJsajW0oDhAQPqmAl2CsIdSq00ZeEANswy',
            'local'=> 'sk_test_51IoNpTFXu7UEcy2xftTt5NSD11C4iCLkpn2tPxtxo1nH7yDtBIEhjjrnf2C5zOupi4hgv89Ngwd6RYrSsPohBLi9004pR820fK',
            'staging' => 'sk_test_jiOaiu4JcFbIW8kKt9y1v0YR00y8ojNNUv',
            'production' => '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'
        ];
        \Stripe\Stripe::setApiKey($key[app()->environment()]);
        $account = Account::find($accountPlanTotal->account_id);
        $subscription = Subscription::where('account_id',$account->id)->orderBy('created_at','DESC')->first();
        $currentSubPlan = SubscriptionPlan::where('id',$account->subscription_plan_id)->first();
        $maxPeople = $currentSubPlan->maxValue('add-people');
        $maxEmail = $currentSubPlan->maxValue('send-email');
        $maxForm = $currentSubPlan->maxValue('add-form');
        if(($accountPlanTotal->total_people > $maxPeople || $accountPlanTotal->total_email > $maxEmail)
        && $currentSubPlan->plan !== 'Free' && $currentSubPlan->plan !== 'Circle'
        && ($subscription->cancel_at === null || $subscription->cancel_at === '1970-01-01 07:30:00')){
            $subscriptionPlan = SubscriptionPlan::where('id',$account->subscription_plan_id+1)->first();
            $retrieveSubscription = \Stripe\Subscription::retrieve($subscription->subscription_id);
            $updateSubscription = \Stripe\Subscription::update($subscription->subscription_id, [
                'cancel_at_period_end' => false,
                'proration_behavior' => 'create_prorations',
                'items' => [
                    [   'id' => $retrieveSubscription->items->data[0]->id,
                        'price' => $subscriptionPlan->price_id,
                    ],
                ],
            ]);
            $account->subscription_plan_id = $account->subscription_plan_id+1;
            $account->subscription_status = $updateSubscription->subscription_status;
            $account->save();
            $user = User::where('currentAccountId',$account->id)->first();
            $subscription = Subscription::updateOrCreate(
                ['account_id' => $account->id],
                [
                    'user_id' => $user->id,
                    'subscription_id' => $updateSubscription->id,
                    'subscription_plan_id' => $account->subscription_plan_id+1,
                    'status' => $updateSubscription->status,
                    'current_plan_start' => date('Y/m/d H:i:s',$updateSubscription->current_period_start),
                    'current_plan_end' => date('Y/m/d H:i:s',$updateSubscription->current_period_end),
                    'trial_start' => date('Y/m/d H:i:s',$updateSubscription->trial_start),
                    'trial_end' => date('Y/m/d H:i:s',$updateSubscription->trial_end),
                ]
            );
        }


}


    public static function boot(){
        parent::boot();

        //activate when plan total is updated
        static::updated(function($accountPlanTotal){
            // $accountPlanTotal->autoUpgrade($accountPlanTotal);
        });
    }


}

