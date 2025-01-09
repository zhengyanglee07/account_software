<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Email
 *
 * @property int $id
 * @property int $account_id
 * @property int $processed_contact_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Email newModelQuery()
 * @method static Builder|Email newQuery()
 * @method static Builder|Email query()
 * @method static Builder|Email whereAccountId($value)
 * @method static Builder|Email whereCreatedAt($value)
 * @method static Builder|Email whereId($value)
 * @method static Builder|Email whereUpdatedAt($value)
 * @method static Builder|ProcessedContact whereProcessedContactId($value)
 * @mixin Eloquent
 */
class EmailUnsubscribe extends Model
{
    protected $table = 'email_unsubscribe';

    protected $fillable = [
        'account_id',
        'processed_contact_id'
    ];
}
