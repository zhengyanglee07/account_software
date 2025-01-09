<?php

namespace App\Models\Promotion;

use Illuminate\Database\Eloquent\Model;
use App\Models\Promotion\Promotion;

class PromotionRedemptionLog extends Model
{
    protected $guarded= [];

    public function calculateStatus($redemptionLog){
       $promotion = Promotion::find($redemptionLog->promotion_id);
       $store_usage = optional($promotion->extraCondition->store_usage);
       $store_limit_value = optional($promotion->extraCondition->store_limit_value);
       if($store_usage !== null && $store_limit_value){
           if($store_usage == $store_limit_value){
               $promotion->promotion_status = 'used';
               $promotion->save();
           }
       }
    }


    public static function boot(){
        parent::boot();

        static::created(function($promotion){
            $promotion->calculateStatus($promotion);
        });

        static::updated(function($promotion){
            $promotion->calculateStatus($promotion);
        });

         static::deleted(function($promotion){

         });

    }
}
