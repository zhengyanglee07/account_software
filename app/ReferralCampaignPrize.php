<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReferralCampaignPrize extends Model
{
    protected $guarded = ['id'];

    public function processedContacts()
    {
        return $this->belongsToMany(ProcessedContact::class, 'processed_contact_prize', 'prize_id')
                    ->withTimestamps()
                    ->using(ProcessedContactPrize::class);
    }
}
