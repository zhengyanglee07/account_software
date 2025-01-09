<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mail;

/**
 * Class AffiliateMemberCommission
 * @package App
 *
 * @property-read \App\AffiliateMemberCampaign $campaign
 * @property-read \App\AffiliateMemberParticipant $participant
 * @property-read \App\Order $order
 *
 * @mixin \Eloquent
 */
class AffiliateMemberCommission extends Model
{
    protected $fillable = [
        'account_id',
        'affiliate_member_campaign_id',
        'affiliate_member_participant_id',
        'order_id',
        'status',
        'level',
        'currency',
        'commission',
        'affiliate_email',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(
            AffiliateMemberCampaign::class,
            'affiliate_member_campaign_id'
        );
    }

    public function participant(): BelongsTo
    {
        return $this
            ->belongsTo(
                AffiliateMemberParticipant::class,
                'affiliate_member_participant_id'
            )
            ->whereNotNull('parent_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

}
