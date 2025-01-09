<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AutomationStatus
 *
 * @property-read string $name
 * @mixin \Eloquent
 */
class AutomationStatus extends Model
{
    protected $table = 'automation_status';
}
