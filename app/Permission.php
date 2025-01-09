<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    public function subplans(){
        return $this->belongsToMany(SubscriptionPlan::class,'subplans_permissions','plan_id','permission_id');
    }

}
