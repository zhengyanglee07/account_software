<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class productOptionValue extends Model
{
    protected $fillable = [
        'product_option_id',
        'label',
        'is_default',
        'sort_order',
        'type_of_single_charge',
        'single_charge',
        'value_1',
        'option',
    ];
}
