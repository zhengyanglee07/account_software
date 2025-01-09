<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AutomationAbandonCartTrigger
 *
 * @mixin \Eloquent
 */
class AutomationAbandonCartTrigger extends Model
{
    protected $fillable = [
        'automation_trigger_id',
    ];

    protected $appends = ['description'];

    /**
     * @return string
     */
    public function getDescriptionAttribute(): string
    {
        return 'visitor abandons cart for a period of time';
    }
}
