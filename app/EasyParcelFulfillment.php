<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class EasyParcelFulfillment
 * @package App
 *
 * @property-read OrderDetail $orderDetail
 *
 * @mixin \Eloquent
 */
class EasyParcelFulfillment extends Model
{
    protected $fillable = [
        'easy_parcel_shipment_id',
        'order_detail_id'
    ];

    public function orderDetail(): BelongsTo
    {
        return $this->belongsTo(OrderDetail::class);
    }
}
