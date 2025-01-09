<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class EcommerceAbandonedCart
 *
 * @mixin \Eloquent
 */
class EcommerceAbandonedCart extends Model
{
    protected $fillable =
    [
        'visitor_id',
        'cart_products',
        'abandoned_at',
    ];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(EcommerceVisitor::class, 'visitor_id');
    }
}
