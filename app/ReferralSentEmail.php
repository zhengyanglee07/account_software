<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReferralSentEmail extends Model
{
    protected $guarded = ['id'];
    protected $fillable = [
        'referral_email_id',
        'processed_contact_id',
        'sent_email_id',
        'referral_campaign_reward_id',
    ];
}
