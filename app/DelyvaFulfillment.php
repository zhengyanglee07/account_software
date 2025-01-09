<?php

namespace App;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DelyvaFulfillment extends Model
{
    // use HasFactory;
    protected $table = 'delyva_fulfillments';

    protected $fillable = [
        'delyva_quotation_id',
        'order_detail_id',
    ];

}