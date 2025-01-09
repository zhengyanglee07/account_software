<?php

namespace App\Models;

use App\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\Model;

class StoreTheme extends Model
{
    use BelongsToAccount;

    protected $fillable = [
        'account_id',
        'styles',
    ];
}
