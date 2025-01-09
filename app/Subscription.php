<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Subscription
 * @package App
 *
 * @property-read SubscriptionPlan $subscriptionPlan
 * @property-read User $user
 *
 * @mixin \Eloquent
 */
class Subscription extends Model
{
    protected $fillable = [
        'account_id',
        'user_id',
        'subscription_id',
        'subscription_plan_id',
        'subscription_plan_price_id',
        'status',
        'current_plan_start',
        'current_plan_end',
        'last_email_reset',
        'trial_start',
        'trial_end',
        'cancel_at',
    ];

    protected $with = ['subscriptionPlanType'];

    public function subplans(){

        return $this->belongsTo(SubscriptionPlan::class,'subscription_plan_id');

    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function subscriptionPlanPrice(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlanPrice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasPermission($permissions){

       return $this->subplans->hasPermission($permissions)->first();

    }

    public function subscriptionPlanType(){
        return $this->belongsTo(SubscriptionPlanPrice::class,'subscription_plan_price_id','id');
    }

    // public function accountPlan(){

    // }


}
