<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    
    protected $guarded= [];

    public $timestamps = false;


    public function ShippingMethodDetail(){
        return $this->belongsTo(ShippingMethodDetail::class,'shipping_detail_id');
    }

    public function ShippingZone(){
        return $this->belongsTo(ShippingZone::class);
    }


    // public static function boot(){
    //     parent::boot();

    //     static::deleted(function($shippingMethod){
    //         $shippingMethod->shippingMethodDetail()->delete();
    //     });
    // }


}
