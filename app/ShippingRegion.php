<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShippingRegion extends Model
{

    protected $casts = [
        'country' => 'array',
        'state'=>'array',
    ];

    protected $guarded= [];




}
