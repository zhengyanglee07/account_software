<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShippingMethodDetail extends Model
{
    protected $guarded= [];

    protected $appends = ['shipping_method'];

    public function shippingZone(){

        return $this->belongsToMany(ShippingZone::class,'shipping_methods','shipping_detail_id','shipping_zone_id')->withPivot('shipping_method');
    }

    public function getShippingMethodAttribute(){
        return $this->shippingZone()->first()->pivot->shipping_method;
    }

}
