<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = [
        'promo_code',
        'expire',
        'reference_id',
        'usage_limit',
        'trial_or_discount',
    ];
}
