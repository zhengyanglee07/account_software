<?php

namespace App;

use Auth;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $guarded = [];
    protected $with = ['products','funnels','forms'];



    public function products()
    {
        return $this->morphedByMany(UsersProduct::class, 'target', 'notification_types')->with('orderDetails');
    }

    public function funnels()
    {
        return $this->morphedByMany(funnel::class, 'target', 'notification_types')->withoutGlobalScopes();
    }

    public function forms()
    {
        return $this->morphedByMany(LandingPageForm::class, 'target', 'notification_types')->with('formContents');
    }

    public static function updateAccountTotalSocialProof(){
        $account = Auth::user()->currentAccount();
        $accountPlan =  Auth::user()->currentAccount()->accountPlanTotal;
        $socialProofCount = Notification::where('account_id',$account->id)->get()->count();
        $accountPlan->total_social_proof = $socialProofCount;
        $accountPlan->save();
    }

    public static function boot(){
        parent::boot();

        static::created(function($notification){
            $notification->updateAccountTotalSocialProof();
        });

        static::deleted(function($notification){
            $notification->updateAccountTotalSocialProof();
        });
    }

}
