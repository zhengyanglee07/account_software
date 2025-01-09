<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Sender
 *
 * @property int $id
 * @property int $account_id
 * @property string $name
 * @property string $email_address
 * @property string|null $status
 * @property string $email_verified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Email[] $emails
 * @property-read int|null $emails_count
 * @method static Builder|Sender newModelQuery()
 * @method static Builder|Sender newQuery()
 * @method static Builder|Sender query()
 * @method static Builder|Sender whereAccountId($value)
 * @method static Builder|Sender whereCreatedAt($value)
 * @method static Builder|Sender whereEmailAddress($value)
 * @method static Builder|Sender whereEmailVerifiedAt($value)
 * @method static Builder|Sender whereId($value)
 * @method static Builder|Sender whereName($value)
 * @method static Builder|Sender whereStatus($value)
 * @method static Builder|Sender whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Sender extends Model
{
    protected $fillable = ['account_id', 'name', 'email_address', 'status', 'email_verified_at'];

    public function emails(): HasMany
    {
        return $this->hasMany(Email::class);
    }
}
