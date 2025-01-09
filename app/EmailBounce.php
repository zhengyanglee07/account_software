<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\EmailBounce
 *
 * @property int $id
 * @property string $email_address
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|EmailBounce newModelQuery()
 * @method static Builder|EmailBounce newQuery()
 * @method static Builder|EmailBounce query()
 * @method static Builder|EmailBounce whereCreatedAt($value)
 * @method static Builder|EmailBounce whereId($value)
 * @method static Builder|EmailBounce whereEmailAddress($value)
 * @method static Builder|EmailBounce whereUpdatedAt($value)
 * @mixin Eloquent
 */
class EmailBounce extends Model
{
    protected $fillable = [
        'account_id',
        'email_address',
        'source_address',
        'type',
        'event_message'
    ];
}
