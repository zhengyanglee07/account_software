<?php

namespace App;

use App\Interfaces\IAutomationStepKind;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AutomationDelay
 *
 * @property int $duration
 * @property string $unit
 * @mixin \Eloquent
 */
class AutomationDelay extends Model implements IAutomationStepKind
{
    protected $fillable = [
        'automation_step_id',
        'duration',
        'unit',
    ];

    protected $appends = ['description'];

    /**
     * @deprecated
     *
     * @return string
     */
    public function getDescriptionAttribute(): string
    {
        $duration = $this->duration;
        $unit = ($this->unit ?? '') . ($duration > 1 ? 's' : '');

        return "Wait for $duration $unit";
    }
}
