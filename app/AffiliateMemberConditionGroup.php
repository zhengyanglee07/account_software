<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class AffiliateMemberConditionGroup
 * @package App
 *
 * @property-read \App\AffiliateMemberGroup[] $groups
 * @property-read \App\AffiliateMemberConditionGroupLevel[] $levels
 *
 * @mixin \Eloquent
 */
class AffiliateMemberConditionGroup extends Model
{
    protected $guarded = ['id'];

    protected $with = ['groups', 'levels'];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(
            AffiliateMemberCampaign::class,
            'affiliate_member_campaign_id'
        );
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(AffiliateMemberGroup::class);
    }

    public function levels(): HasMany
    {
        return $this->hasMany(AffiliateMemberConditionGroupLevel::class);
    }
}
