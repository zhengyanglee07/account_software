<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\EmailStatus
 *
 * @property int $id
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|EmailStatus newModelQuery()
 * @method static Builder|EmailStatus newQuery()
 * @method static Builder|EmailStatus query()
 * @method static Builder|EmailStatus whereCreatedAt($value)
 * @method static Builder|EmailStatus whereId($value)
 * @method static Builder|EmailStatus whereStatus($value)
 * @method static Builder|EmailStatus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EmailStatus extends Model
{
    protected $table = 'email_status';
}
