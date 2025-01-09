<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class LalamoveDeliveryOrder
 *
 * @property string $order_ref
 * @property-read LalamoveQuotation $lalamoveQuotation
 * @property-read LalamoveDeliveryOrderDetail $lalamoveDeliveryOrderDetail
 *
 * @mixin \Eloquent
 */
class LalamoveDeliveryOrder extends Model
{
    protected $fillable = [
        'lalamove_quotation_id',

        // if order is created successfully
        'customer_order_id',  // lalamove deprecated order id
        'order_ref',  // lalamove order id
    ];

    protected $with = ['lalamoveDeliveryOrderDetail'];

    public function lalamoveQuotation(): BelongsTo
    {
        return $this->belongsTo(LalamoveQuotation::class);
    }

    public function lalamoveDeliveryOrderDetail(): HasOne
    {
        return $this->hasOne(LalamoveDeliveryOrderDetail::class);
    }
}
