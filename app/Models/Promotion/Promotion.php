<?php

namespace App\Models\Promotion;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use PDO;

class Promotion extends Model
{
    protected $guarded= [];
    protected $with = ['promotionType','extraCondition'];
    // public $timestamps = false;
    public static function findByCode($code,$account_id){
        return self::whereRaw('BINARY discount_code = ?',$code)->where('account_id',$account_id)
        ->where('start_date','<=', Carbon::now())->with('extraCondition','promotionType')->first();
    }

    public function promotionType(){
        return $this->morphTo(__FUNCTION__,'discount_type','discount_id');
    }

    public function discount($totalAfterDiscount,$currencyInfo=null,$shippingCharge=null){
        return $this->promotionType->discount($totalAfterDiscount,$currencyInfo,$shippingCharge);
    }

    public function checkRequirement($valueAfterDiscount){
        return $this->promotionType->checkRequirement($valueAfterDiscount);
    }

    public function checkExpiry(){
        if($this->promotion_status == 'expired') return false;
        if(!$this->is_expiry) return true;
        return Carbon::parse($this->end_date)->gte(now());
    }

    public function checkStartDate(){
        return Carbon::parse($this->start_date)->lte(now());
    }

    public function checkStoreUsage(){
        return $this->extraCondition->checkStoreUsage();
    }

    public function checkCustomerUsage($customer_email){
        return $this->extraCondition->checkCustomerUsage($this->id,$customer_email);
    }

    public function checkTargetCustomer($customer_email){
        return $this->extraCondition->checkTargetCustomer($customer_email);
    }

    public function extraCondition(){
        return $this->belongsTo(PromotionExtraCondition::class,'extra_condition_id');
    }

    public function checkValid($param){
        return
            $this->checkStartDate() && $this->checkExpiry() &&
            $this->checkStoreUsage() && $this->checkRequirement($param) &&
            $this->checkCustomerUsage($param['customerEmail']) && $this->checkTargetCustomer($param['customerEmail']);
    }

    public function checkValidShipping($country){
        return $this->promotionType->checkValidShipping($country);
    }

    public function errorMessage($param){
        $errorMessage = null;
        if(!$this->checkExpiry()){
            $errorMessage = 'Promo code is expired';
        }else if(!$this->checkStoreUsage()){
            $errorMessage = 'Promo code has fully redeemed';
        }else if (!$this->checkCustomerUsage($param['customerEmail'])){
            $errorMessage = "You have reached the limit of redeemed";
        }else if (!$this->checkRequirement($param)){
            $errorMessage = "Invalid promo code";
        }else if(!$this->checkTargetCustomer($param['customerEmail'])){
            $errorMessage = "Sorry email not valid to applied this promo code";
        }
        return $errorMessage;
    }

    public function checkValidDiscountProduct($param){
        return $this->promotionType->checkValidDiscountProduct($param);
    }

}
