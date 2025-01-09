<?php

namespace App;

use App\Notifications\AffiliateMemberPasswordResetNotification;
use App\Notifications\VerifyAffiliateMemberEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Account;
use Laravel\Sanctum\HasApiTokens;
use Mail;

/**
 * Class AffiliateMemberAccount
 * @package App
 *
 * @property int $id
 * @property string $reference_key
 * @property int $account_id
 * @property string $email
 * @property string $first_name
 * @property string $last_name
 * @property string $referral_identifier
 * @property-read Account $account
 * @property-read \App\AffiliateMemberParticipant $participant
 * @property-read \App\AffiliateMemberReferral[] $referrals
 *
 * @mixin \Eloquent
 */
class AffiliateMemberAccount extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, SoftDeletes, HasApiTokens;

    protected $fillable = [
        'reference_key',
        'account_id',
        'first_name',
        'last_name',
        'referral_identifier',
        'email',
        'email_verified_at',
        'password',
        'address',
        'city',
        'state',
        'zipcode',
        'country',
        'paypal_email',
        'remember_token',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Parent's account, use this relation sparingly
     * Avoid revealing sensitive parent info to affiliate
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function participant(): HasOne
    {
        return $this->hasOne(AffiliateMemberParticipant::class);
    }

    public function referrals(): HasManyThrough
    {
        return $this->hasManyThrough(AffiliateMemberReferral::class, AffiliateMemberParticipant::class);
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyAffiliateMemberEmail);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new AffiliateMemberPasswordResetNotification($token));
    }

    public static function boot()
    {
        parent::boot();
        static::created(function ($affiliateAccount) {
            $affiliateAccount->calculateTotalAffiliateMember($affiliateAccount);
            $account = Account::find($affiliateAccount->account_id);
            $user = $account->user->first();
            if (!$account?->affiliateMemberSettings?->auto_approve_affiliate) {
                Mail::to($user?->email)->send(new \App\Mail\AffiliateMemberApprovalEmail($affiliateAccount));
            }
        });

        static::deleted(function ($affiliateAccount) {
            $affiliateAccount->calculateTotalAffiliateMember($affiliateAccount);
        });
    }

    protected function calculateTotalAffiliateMember($affiliateAccount)
    {
        $account = Account::find($affiliateAccount->account_id);
        $accountPlan = $account->accountPlanTotal;
        $accountPlan->total_affiliate_member = $account->affiliateMembers->count();
        $accountPlan->save();
    }

    public function name()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
