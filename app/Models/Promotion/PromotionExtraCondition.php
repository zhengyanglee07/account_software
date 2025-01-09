<?php

namespace App\Models\Promotion;

use App\Segment;
use Illuminate\Database\Eloquent\Model;

class PromotionExtraCondition extends Model
{
    // public $timestamps = false;
    protected $guarded= [];

    public function checkStoreUsage(){
        if($this->store_limit_type == 'unlimited'){
            return true;
        }

        return $this->store_usage < $this->store_limit_value;
    }

    public function checkCustomerUsage($id,$customer_email){

        // dd($customer_email);
        if($this->customer_limit_type == 'unlimited'){
            return true;
        }
        $redeemLog = PromotionRedemptionLog::where('promotion_id',$id)->where('customer_email',$customer_email)->first();
        if($redeemLog == null){
            return true;
        }
        return $redeemLog->applied_usage < $this->customer_limit_value;
    }

    public function checkTargetCustomer($customer_email=null){
        if($this->target_customer_type == 'all') return true;

        $target = json_decode($this->target_value, true);

        $segments = Segment::whereIn('id', $target)->get();
        $emailArr = [];
        foreach($segments as $segment){
            foreach($segment->contacts() as $contact){
                if(!in_array($contact->email,$emailArr)){
                    array_push($emailArr,$contact->email);
                }
            }
        }
        return in_array($customer_email,$emailArr);
    }
}
