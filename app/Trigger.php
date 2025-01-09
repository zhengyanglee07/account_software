<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Trigger
 *
 * @property int $id
 * @property string $type
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Trigger newModelQuery()
 * @method static Builder|Trigger newQuery()
 * @method static Builder|Trigger query()
 * @method static Builder|Trigger triggerId($triggerType)
 * @method static Builder|Trigger whereCreatedAt($value)
 * @method static Builder|Trigger whereId($value)
 * @method static Builder|Trigger whereType($value)
 * @method static Builder|Trigger whereName($value)
 * @method static Builder|Trigger whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Trigger extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * @param Builder $query
     * @param string $triggerType
     * @return mixed
     */
    public function scopeTriggerId($query, string $triggerType)
    {
        return $query->where('type', $triggerType)->first()->id;
    }
}
