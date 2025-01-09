<?php

namespace App\Models\Promotion;

use Illuminate\Database\Eloquent\Model;
use App\Category;
use App\Traits\CurrencyConversionTraits;

class PromotionProductDiscount extends Model
{
    use CurrencyConversionTraits;
    protected $table = "promotion_product_discounts";
    protected $guarded = [];

    public function discount($productPrice,$currencyInfo = null,$shippingCharge = null){
        if($this->product_discount_type == 'percentage') {
            $discountCap = $this->convertCurrency($this->product_discount_cap,$currencyInfo['currency'], false, false, $currencyInfo['account_id']);
            $value = $productPrice * ($this->product_discount_value / 100);
            if($this->product_discount_cap != '' && $this->product_discount_cap != null){
                $value = ($value > $discountCap) ? $discountCap : $value;
            }
            return floatval($value);
        }

        $fixedDiscount = floatval($this->convertCurrency($this->product_discount_value, $currencyInfo['currency'], false, false, $currencyInfo['account_id']));
        $discountValue = ($productPrice - $fixedDiscount <= 0 ) ? $productPrice : $fixedDiscount;
        return $discountValue;
    }

    public function checkRequirement($request){
        if($this->discount_target_type =='all-product'){
            return array_reduce($request['productArray'],function($acc,$product){
                return $acc + $product->qty;
            }) >= $this->minimum_quantity;
        }else if($this->discount_target_type =='specific-product'){
            foreach($request['productArray'] as $product){
                if($product->qty >= $this->minimum_quantity){
                    foreach(json_decode($this->target_value) as $value){
                        if($value->productId == $product->id && !$value->hasVariant){
                            return true;
                        }else if($value->productId == $product->id && $value->hasVariant){
                            foreach( $value->combinationVariant as $combId){
                                $combination = $combId->combinationId;
                                $selectedCombination = $this->variantCombinationId($product);
                                $combinationResult = array_diff($combination, $selectedCombination);
                                if(count($combinationResult) == 0) {
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
        }else if($this->discount_target_type =='specific-category'){
            $promoProductId = [];
            $categories = Category::find(json_decode($this->target_value));
            foreach($categories as $category){
                $totalCategoryQuantity = 0;
                foreach($request['productArray'] as $product){
                    foreach($category->products as $catProduct){
                        if($catProduct->id == $product->id && !in_array($product->id,$promoProductId)){
                            $totalCategoryQuantity += $product->qty;
                            array_push($promoProductId,$product->id);
                        }
                    }
                    if($totalCategoryQuantity >= $this->minimum_quantity){
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function checkValidDiscountProduct($request){
        if($this->discount_target_type =='all-product'){
            return array_reduce($request['productArray'],function($acc,$product){
                return $acc + $product->qty;
            }) >= $this->minimum_quantity;
        }else if($this->discount_target_type =='specific-product'){
            if($request['product']->qty >= $this->minimum_quantity){
                foreach(json_decode($this->target_value) as $value){
                    if($value->productId == $request['product']->id && !$value->hasVariant){
                        return true;
                    }else if($value->productId == $request['product']->id && $value->hasVariant){
                        foreach( $value->combinationVariant as $combId){
                            $combination = $combId->combinationId;
                            $selectedCombination = $this->variantCombinationId($request['product']);
                            $combinationResult = array_diff($combination, $selectedCombination);
                            if(count($combinationResult) == 0){
                                return true;
                            }
                        }
                    }
                }
            }
        }else if($this->discount_target_type =='specific-category'){
            $totalqty = 0;
            $promoProductId = [];
            $categories = Category::find(json_decode($this->target_value));
            foreach($request['productArray'] as $product){
                foreach($categories as $category){
                    foreach($category->products as $catProduct){
                        if($catProduct->id == $product->id && !in_array($product->id,$promoProductId)){
                            $totalqty += $product->qty;
                            array_push($promoProductId,$product->id);
                        }
                    }
                }
            }
            if($totalqty >= $this->minimum_quantity && in_array($request['product']->id,$promoProductId)){
                return true;
            }
        }
        return false;
    }
    public function variantCombinationId($product){
        $combinationId = [];
        foreach($product->variant_details as $variant) {
            if ($variant->reference_key == $product->variantRefKey) {
                for ($i = 1; $i <= 5; $i++) {
                    if ($variant->{'option_' . $i . '_id'} != null) {
                        array_push($combinationId, $variant->{'option_' . $i . '_id'});
                    }
                }
            }
        }
        return $combinationId;
    }
}
