<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class AffiliateMemberLifetimeCommission
 * @package App
 *
 * @property int $id
 * @property int $account_id
 * @property int $processed_contact_id
 * @property int $affiliate_member_participant_id
 *
 * @mixin \Eloquent
 */
class AffiliateMemberLifetimeCommission extends Model
{
    protected $fillable = [
        'account_id',
        'affiliate_member_participant_id',
        'processed_contact_id',
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
}
