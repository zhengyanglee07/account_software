<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class AccountOauth
 * @package App
 *
 * @mixin \Eloquent
 */
class AccountOauth extends Model
{
    protected $fillable = [
        'account_id',
        'social_media_provider_id',
        'token',
        'refresh_token',
        'expires_in',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function socialMediaProvider(): BelongsTo
    {
        return $this->belongsTo(SocialMediaProvider::class);
    }
}
