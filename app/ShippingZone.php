<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    // protected $casts = [
    //     'country' => 'array',
    //     'state'=>'array',
    // ];

    protected $guarded = [];
    protected $with = ['shippingRegion', 'shippingMethodDetails'];


    public function shippingMethod()
    {

        return $this->hasMany(ShippingMethod::class);
    }

    public function shippingMethodDetails()
    {
        return $this->belongsToMany(ShippingMethodDetail::class, 'shipping_methods', 'shipping_zone_id', 'shipping_detail_id')->withPivot('shipping_method');
    }


    public function shippingRegion()
    {
        return $this->hasMany(ShippingRegion::class, 'zone_id');
    }

    public function checkValidShippingZone($request)
    {
        $shippingRegion = $this->shippingRegion;
        foreach ($shippingRegion as $region) {
            if ($region->country == $request['country']) {
                if ($region->region_type == 'zipcodes') {
                    if (in_array($request['zipcode'], json_decode($region->zipcodes))) {
                        return true;
                    }
                } else {
                    $states = array_map(function ($value) {
                        return $value['stateName'];
                    }, $region->state);
                    if (in_array($request['state'], $states)) {
                        return true;
                    }
                }
            }
        }
        $allZone = ShippingZone::where('account_id', $request['account_id'])->get();
        $compareArr = [];
        foreach ($allZone as $zone) {
            foreach ($zone->shippingRegion as $zoneRegion) {
                array_push($compareArr, $zoneRegion->country);
            }
        }
        return in_array('Rest of world', $compareArr) && !in_array($request['country'], $compareArr);
    }





    public static function boot()
    {
        parent::boot();

        static::deleting(function ($shippingZone) {
        });
    }
}
