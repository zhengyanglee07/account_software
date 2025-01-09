<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderRefundLog extends Model
{
    protected $fillable = [
        'order_id',
        'transaction_id',
        'refundAmount',
        'refunded_reason',



    ];
}
