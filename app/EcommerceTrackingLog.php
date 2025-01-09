<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EcommerceTrackingLog extends Model
{
    protected $fillable =
    [
        'visitor_id',   
        'type',
        'value',
        'is_conversion',
    ];
}
