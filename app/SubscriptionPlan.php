<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'plan',
        'max_people',
        'max_product',
        'max_storage',
        'max_user',
        'max_form_submission',
        'max_domain',
        'max_funnel',
        'max_landingpage',
        'segment_and_tag',
        'import',
        'export',
        'email',
        'automation',
        'automation',
        'coupons_discount_and _points',
        'affiliate',
        'price',
        'price_id',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscriptionPlanPrice()
    {
        return $this->hasMany(SubscriptionPlanPrice::class);
    }

    public function permissions(){
        return $this->belongsToMany(Permission::class,'subplans_permissions','plan_id')->withPivot('max_value');
    }

    public function hasPermission($permissions){

       return $this->permissions()->where('slug',$permissions);
    }

    public function maxValue($slug){
        return $this->permissions->where('slug',$slug)->first()->pivot->max_value;
    }



}
