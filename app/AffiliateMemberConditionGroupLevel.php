<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class AffiliateMemberConditionGroupLevel
 * @package App
 *
 * @mixin \Eloquent
 */
class AffiliateMemberConditionGroupLevel extends Model
{
    protected $guarded = ['id'];

    public function conditionGroup(): BelongsTo
    {
        return $this->belongsTo(
            AffiliateMemberConditionGroup::class,
            'affiliate_member_condition_group_id'
        );
    }
}
