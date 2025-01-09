<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class LalamoveQuotation
 *
 * @property string $scheduled_at
 * @property string $service_type
 * @property array $stops
 * @property array $deliveries
 * @property array $requester_contacts
 * @property array $special_requests
 * @property string $total_fee_amount
 * @property string $total_fee_currency
 * @property-read Order $order
 * @property-read LalamoveDeliveryOrder $lalamoveDeliveryOrder
 *
 * @mixin \Eloquent
 */
class LalamoveQuotation extends Model
{
    protected $fillable = [
        'order_id',

        // request body of /v2/quotations
        'scheduled_at',
        'service_type',
        'stops',
        'deliveries',
        'requester_contacts',
        'special_requests',

        // response from /v2/quotations
        'total_fee_amount',
        'total_fee_currency',
    ];

    protected $casts = [
        'stops' => 'array',
        'deliveries' => 'array',
        'requester_contacts' => 'array',
        'special_requests' => 'array'
    ];

    protected $with = ['lalamoveDeliveryOrder'];

    // hypershapes order
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function lalamoveDeliveryOrder(): HasOne
    {
        return $this->hasOne(LalamoveDeliveryOrder::class);
    }
}
