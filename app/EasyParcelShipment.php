<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class EasyParcelShipment
 * @package App
 *
 * @property string $service_id
 * @property-read Order $order
 * @property-read \App\EasyParcelShipmentParcel $easyParcelShipmentParcel
 *
 * @mixin \Eloquent
 */
class EasyParcelShipment extends Model
{
    protected $fillable = [
        'order_id',

        // service (before order is made)
        'service_detail',
        'service_id',
        'service_type',
        'service_name',
        'delivery',

        // courier
        'courier_name',

        // price (well these are not really needed, put here just for reference
        'price',
        'addon_price',
        'shipment_price',

        // additional cols after order is made
        'order_number',  // EasyParcel order number, don't confuse with hypershapes order number
        'order_status'   // to record EasyParcel order status, e.g. Waiting for Payment, Insufficient Credit, etc
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function easyParcelShipmentParcel(): HasOne
    {
        return $this->hasOne(EasyParcelShipmentParcel::class);
    }

    public function easyParcelFulfillments(): HasMany
    {
        return $this->hasMany(EasyParcelFulfillment::class);
    }
}
