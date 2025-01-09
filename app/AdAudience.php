<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class AdAudience
 * @package App
 *
 * @property int $id
 * @property int $segment_id
 * @property int $account_oauth_id
 * @property string $list_name
 * @property int $list_id
 * @property string $sync_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @mixin \Eloquent
 */
class AdAudience extends Model
{
    protected $fillable = [
        'segment_id',
        'account_oauth_id',
        'list_name',
        'list_id',
        'sync_status'
    ];

    public function segment(): BelongsTo
    {
        return $this->belongsTo(Segment::class);
    }

    public function accountOauth(): BelongsTo
    {
        return $this->belongsTo(AccountOauth::class);
    }
}
