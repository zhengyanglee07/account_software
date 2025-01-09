<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class productOption extends Model
{
    protected $fillable = [
        'account_id',
        'name',
        'display_name',
        'type',
        'help_text',
        'tool_tips',
        'is_range',
        'at_least',
        'up_to',
        'is_shared',
        'is_required',
        'sort_order',
        'is_total_Charge',
        'global_priority',
        'total_charge_amount',
    ];

    public function values()
    {
        return $this->hasMany(productOptionValue::class);
    }
}
