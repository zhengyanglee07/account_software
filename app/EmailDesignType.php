<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\EmailDesign
 *
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|EmailDesign newModelQuery()
 * @method static Builder|EmailDesign newQuery()
 * @method static Builder|EmailDesign query()
 * @method static Builder|EmailDesign whereCreatedAt($value)
 * @method static Builder|EmailDesign whereId($value)
 * @method static Builder|EmailDesign whereName($value)
 * @mixin \Eloquent
 */
class EmailDesignType extends Model
{
}
