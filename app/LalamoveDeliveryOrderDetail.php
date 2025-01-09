<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class LalamoveDeliveryOrderDetail
 *
 * @mixin \Eloquent
 */
class LalamoveDeliveryOrderDetail extends Model
{
    protected $fillable = [
        'lalamove_delivery_order_id',
        'driver_id',
        'share_link',
        'status',
        'price'
    ];

    protected $casts = [
        'price' => 'array'
    ];
}
