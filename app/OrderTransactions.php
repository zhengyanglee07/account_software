<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderTransactions extends Model
{
    protected $fillable = [
        'order_id',
        'transaction_key',
        'refundTotal',
        'total',
        'paid_at',
        'payment_status',


    ];


    public function order(): HasMany
    {
        return $this->hasMany(OrderTransactions::class);
    }

}
