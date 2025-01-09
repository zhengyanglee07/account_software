<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EcommerceNavBar extends Model
{
    //
    protected $fillable = [
        'account_id',
        'title',
        'active_status',
        'menu_items',
        
    ];
}
