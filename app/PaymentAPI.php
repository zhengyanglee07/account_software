<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentAPI extends Model
{
    protected $fillable = [
        'account_id',
        'publishable_key',
        'secret_key',
        'payment_methods',
        'display_name',
        'display_name2',
        'description',
        'enabled_at',
        'enable_fpx',
    ];
}
