<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mail;

/**
 * Class AffiliateMemberCommissionPayout
 * @package App
 *
 * @property int $account_id
 * @property int $affiliate_member_participant_id
 * @property string $status
 * @property string $paid_at
 * @property int $amount
 * @property string $currency
 * @property-read \App\AffiliateMemberParticipant $participant
 *
 * @mixin \Eloquent
 */
class AffiliateMemberCommissionPayout extends Model
{
    protected $fillable = [
        'account_id',
        'affiliate_member_participant_id',

        // Note: a payout has three statuses:
        //      - pending
        //      - paid
        //      - disapproved
        'status',

        // paid_at will be null in all statuses except 'paid'
        'paid_at',

        'currency',
        'amount',
    ];

    public function participant(): BelongsTo
    {
        return $this
            ->belongsTo(
                AffiliateMemberParticipant::class,
                'affiliate_member_participant_id'
            )
            ->whereNotNull('parent_id');
    }

    public static function boot(){
        parent::boot();
        static::created(function($payout){
            $account = Account::find($payout->account_id);
            $user = $account->user->first();
            $affiliateAccount = $payout->participant->member;
            if($affiliateAccount){
                Mail::to($user?->email)->send(new \App\Mail\AffiliateMemberRequestPayoutEmail($affiliateAccount, $payout));
            }
        });

    }
}
