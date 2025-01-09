<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class AffiliateMemberGroup
 * @package App
 *
 * @property int $id
 * @property int $account_id
 * @property-read \Illuminate\Database\Eloquent\Collection $participants
 *
 * @mixin \Eloquent
 */
class AffiliateMemberGroup extends Model
{
    protected $guarded = ['id'];

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(AffiliateMemberParticipant::class);
    }
}
