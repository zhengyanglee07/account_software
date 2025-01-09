<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;
// use Illuminate\Notifications\Notifiable;
use App\Notifications\AffiliateResetPasswordNotification;


class AffiliateUser extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;
    use SoftDeletes;

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new AffiliateResetPasswordNotification($token));
    }

    protected $guarded = ['id'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['email_verified_at' => 'datetime'];

    protected $appends = ['fullName'];

    public function affiliateDetail(){
        return $this->hasOne(AffiliateDetail::class,'affiliate_userid');
    }

    public function referrals(){
        return $this->hasMany(AffiliateReferralLog::class,'affiliate_id');
    }

    public function affiliateReferrals(){
        return $this->hasMany(AffiliateReferralLog::class,'affiliate_id');
    }

    public function affiliateCommissionLogs(){
        return $this->hasMany(AffiliateCommissionLog::class,'affiliate_id','id');
    }

    public function getFullNameAttribute(){
        return $this->first_name.' '.$this->last_name;
    }

    public function tier(){
        $parentTier = 'Standard';
        $referrals = AffiliateReferralLog::where(['refer_from_affiliate_id'=>$this->affiliate_unique_id, 'subscription_lock_status' => 'paid'])->get();
            if(count($referrals) >= (app()->environment('staging') ? 1 : 10))  $parentTier = 'Pro';
            if(count($referrals) >= (app()->environment('staging') ? 5 : 50))  $parentTier = 'Expert';
        return $parentTier;
    }

    public function accountUser(){
        return User::where('email', $this->email)->first();
    }

    public function successReferralCount(){
        $referrals = AffiliateReferralLog::where(['refer_from_affiliate_id'=>$this->affiliate_unique_id, 'subscription_lock_status' => 'paid'])->get();
        return $referrals->count();
    }

}
