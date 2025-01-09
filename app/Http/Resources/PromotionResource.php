<?php

namespace App\Http\Resources;
use Carbon\Carbon;

use Illuminate\Http\Resources\Json\JsonResource;

class PromotionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $discountType = 'freeShipping';
        $discountValueType = 'percentage';
        $discountValue = 0;
        $discountCap = 0;
        $productDiscountType = 'all-product';
        $selectedProduct = [];
        $selectedCategory = [];
        $minimumQuantity = 1;

        if($this->promotion_category == 'Order'){
            $discountType ='orderBased';
            $discountValueType = $this->promotionType->order_discount_type;
            $discountValue = ($discountValueType == 'percentage') ? intval($this->promotionType->order_discount_value) : $this->promotionType->order_discount_value;
            $discountCap = $this->promotionType->order_discount_cap;
        }else if($this->promotion_category == 'Product'){
            $discountType = 'productBased';
            $discountValueType = $this->promotionType->product_discount_type;
            $discountValue =  ($discountValueType == 'percentage') ? intval($this->promotionType->product_discount_value) : $this->promotionType->product_discount_value;
            $discountCap = $this->promotionType->product_discount_cap;
            $productDiscountType = $this->promotionType->discount_target_type;
            $minimumQuantity = $this->promotionType->minimum_quantity;
            if($this->promotionType->discount_target_type =='specific-product'){
                $selectedProduct = json_decode($this->promotionType->target_value);
            }else if($this->promotionType->discount_target_type =='specific-category'){
                $selectedCategory = json_decode($this->promotionType->target_value);
            }
        }


        return [
            'id' => $this->id,
            'promoCode' => $this->discount_code,
            'promoTitle' => $this->promotion_name,
            'displayTitle' =>$this->display_name,
            'discountType' => $discountType,
            'discountValueType' => $discountValueType,
            'discountValue' => $discountValue,
            'discountCap' => $discountCap,
            'discountCategory' => $this->promotion_category,
            'minimumType' => $this->promotionType->requirement_type,
            'minimumValue' => $this->promotionType->requirement_value,
            'isExpiryDate' => $this->is_expiry,
            'startDate' =>$this->start_date,
            'endDate' => $this->end_date ==null ? null : $this->end_date,
            'countryType' => $this->promotion_category == 'Shipping' ? $this->promotionType->applied_countries_type : 'all' ,
            'selectedCountries' => $this->promotion_category == 'Shipping' ? json_decode($this->promotionType->applied_countries) : [],
            'selectedProduct' => $selectedProduct,
            'selectedCategory'=> $selectedCategory,
            'productDiscountType' => $productDiscountType,
            'minimumQuantity' => $minimumQuantity,
            'storeUsageLimitType' => $this->extraCondition->store_limit_type,
            'storeUsageValue' => $this->extraCondition->store_limit_value,
            'customerUsageLimitType' => $this->extraCondition->customer_limit_type,
            'customerUsageValue' => $this->extraCondition->customer_limit_value,
            'targetCustomerType' => $this->extraCondition->target_customer_type,
            'targetValue' => $this->extraCondition->target_customer_type == 'all' ? [] : json_decode($this->extraCondition->target_value),
        ];
    }
}
