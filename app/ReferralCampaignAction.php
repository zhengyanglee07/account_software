<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReferralCampaignAction extends Model
{
    protected $guarded = ['id'];

    public function actionType(){
        return $this->belongsTo(ReferralCampaignActionType::class,'action_type_id');
    }
}
