<?php

namespace App;

use App\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\Model;

class FacebookPixel extends Model
{
    // use BelongsToAccount;

    protected $fillable = [
        'account_id',
        'pixel_id',
        'api_token',
        'facebook_selected',
    ];
}
