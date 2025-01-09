<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;

/**
 * Class AutomationPlaceOrderTrigger
 * @package App
 *
 * @mixin \Eloquent
 */
class AutomationPlaceOrderTrigger extends Model
{
    protected $fillable = [
        'automation_trigger_id',
        'filters',
    ];

    protected $appends = ['description'];

    protected $casts = ['filters' => 'array'];

    /**
     * @return string
     */
    public function getDescriptionAttribute(): string
    {
        $user = Auth::user();

        if (!$user) {
            return '';
        }

        return 'order is placed and matches applied filters';
    }
}
