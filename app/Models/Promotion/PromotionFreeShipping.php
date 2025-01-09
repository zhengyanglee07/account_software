<?php

namespace App\Models\Promotion;

use App\ShippingZone;
use App\Traits\CurrencyConversionTraits;
use Illuminate\Database\Eloquent\Model;

class PromotionFreeShipping extends Model
{
    use CurrencyConversionTraits;
    protected $guarded = [];
    protected $table = "promotion_free_shippings";

    public function discount($subtotal, $currencyInfo = null, $shippingCharge = null)
    {
        return ($shippingCharge !== null) ? $shippingCharge : 0;
    }

    public function checkRequirement($request)
    {
        $zipcode = $request['customerInfo']->shipping->zipCode ?? null;
        $state = $request['customerInfo']->shipping->state ?? null;
        $currencyInfo = $request['currencyInfo'];
        if (($this->requirement_type == "none" || $request['subTotal'] >= $this->convertCurrency($this->requirement_value, $currencyInfo['currency'], false, false, $currencyInfo['account_id'])) && $request['isPhysicalProduct']) {
            if ($this->applied_countries_type == 'all') {
                return true;
            } else {
                $zones = ShippingZone::find(json_decode($this->applied_countries));
                $compareArr = [];
                foreach ($zones as $zone) {
                    foreach ($zone->shippingRegion as $region) {
                        array_push($compareArr, $region->country);
                        if ($region->country == $request['country']) {
                            if ($region->region_type == 'zipcodes') {
                                if (in_array($zipcode, json_decode($region->zipcodes))) {
                                    return true;
                                }
                            } else {
                                $states = array_map(function ($value) {
                                    return $value['stateName'];
                                }, $region->state);
                                if (in_array($state, $states)) {
                                    return true;
                                }
                            }
                        }
                    }
                }
                if (in_array('Rest of world', $compareArr) && !in_array($request['country'], $compareArr)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function checkValidShipping($country)
    {
        if ($this->applied_countries_type == 'allCountries') {
            return true;
        } else {
            $appliedCountries = JSON_decode($this->applied_countries);
            foreach ($appliedCountries as $appliedCountry) {
                if ($appliedCountry->country == $country) {
                    return true;
                } else if ($appliedCountry->country == 'Rest of world') {
                    return true;
                }
            }
            return false;
        }
    }
}
