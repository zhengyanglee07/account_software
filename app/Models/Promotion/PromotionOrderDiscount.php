<?php

namespace App\Models\Promotion;

use App\Traits\CurrencyConversionTraits;
use Illuminate\Database\Eloquent\Model;

class PromotionOrderDiscount extends Model
{
    use CurrencyConversionTraits;
    protected $table = "promotion_order_discounts";
    protected $guarded= [];

    public function discount($totalAfterDiscount,$currencyInfo = null){
        if($totalAfterDiscount !== 0){
            if($this->order_discount_type == 'percentage') {
               $discountCap = $this->convertCurrency($this->order_discount_cap,$currencyInfo['currency'], false, false, $currencyInfo['account_id']);
               $value = $totalAfterDiscount * ($this->order_discount_value / 100);
                if($this->order_discount_cap != '' && $this->order_discount_cap != null){
                    $value = ($value > $discountCap) ? $discountCap : $value;
                }
                return floatval($value);
            }
            $fixedDiscount = $this->convertCurrency($this->order_discount_value,$currencyInfo['currency'], false, false, $currencyInfo['account_id']);
            $discountValue = ($totalAfterDiscount - $fixedDiscount <= 0 ) ? $totalAfterDiscount : $fixedDiscount;
            return $discountValue;
        }
        return 0;
    }

    public function checkRequirement($request){
        $currencyInfo = $request['currencyInfo'];
        if($request['totalAfterDiscount'] == 0) return false;
        if($this->requirement_type == "none") return true;
        if($this->requirement_value == 0) return false;
        return $request['totalAfterDiscount'] >= $this->convertCurrency($this->requirement_value,$currencyInfo['currency'], false, false, $currencyInfo['account_id']);
    }

}
