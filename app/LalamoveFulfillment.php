<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class LalamoveFulfillment
 * @package App
 *
 * @mixin \Eloquent
 */
class LalamoveFulfillment extends Model
{
    protected $fillable = [
        'lalamove_quotation_id',
        'order_detail_id'
    ];

    public function orderDetail(): BelongsTo
    {
        return $this->belongsTo(OrderDetail::class);
    }
}

