<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

/**
 * Class AutomationDateBasedTrigger
 *
 * @property int $id
 * @property int $automation_trigger_id
 * @property int $execution_time_period
 * @property string $execution_time_unit
 * @property string $execution_time_direction
 * @property string $target
 * @property string $target_specific_date
 * @property int $repeat_yearly
 *
 * @mixin \Eloquent
 */
class AutomationDateBasedTrigger extends Model
{
    protected $fillable = [
        'automation_trigger_id',
        'execution_time_period',
        'execution_time_unit',
        'execution_time_direction',
        'target',
        'target_specific_date',
        'repeat_yearly',
    ];

    protected $appends = ['description'];

    public function processedContacts(): BelongsToMany
    {
        return $this->belongsToMany(ProcessedContact::class)->withPivot('triggered_at');
    }

    /**
     * @return string
     */
    public function getDescriptionAttribute(): string
    {
        $period = $this->execution_time_period;
        $unit = $this->execution_time_unit;
        $direction = $this->execution_time_direction;

        switch ($this->target) {
            case 'people_profile_birthday':
                $target = 'customer birthday';
                break;

            case 'people_profile_acquisition_date':
                $target = 'customer acquisition date';
                break;

            case 'people_profile_date_type_custom_field':
                $target = 'date type custom field';
                break;

            case 'specific_date':
                $target = 'specific date of ' . Carbon::parse($this->target_specific_date)->format('Y/m/d');
                break;

            default:
                $target = 'Unknown target';
        }

        $duration = $direction !== 'on'
            ? "$period $unit $direction"
            : ucfirst($direction);

        $repeatYearly = '- repeat yearly: ' . ($this->repeat_yearly ? 'yes' : 'no');

        return "$duration $target $repeatYearly";
    }
}
