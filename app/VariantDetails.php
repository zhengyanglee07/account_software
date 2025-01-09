<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VariantDetails extends Model
{
    protected $guarded = [];

    public function variantValue1(){
        return $this->belongsTo(VariantValue::class,'option_1_id');
    }

    public function variantValue2(){
        return $this->belongsTo(VariantValue::class,'option_2_id');
    }

    public function variantValue3(){
        return $this->belongsTo(VariantValue::class,'option_3_id');
    }

    public function variantValue4(){
        return $this->belongsTo(VariantValue::class,'option_4_id');
    }

    public function variantValue5(){
        return $this->belongsTo(VariantValue::class,'option_5_id');
    }
}
