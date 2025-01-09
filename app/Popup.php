<?php

namespace App;

use App\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Popup extends Model
{
    use BelongsToAccount, SoftDeletes;

    protected $fillable =
    [
        'account_id',
        'name',
        'type',
        'is_publish',
        'triggers',
        'display_frequency',
        'element',
        'design',
        'configurations',
        'reference_key',
    ];

    protected $casts = [
        'configurations' => 'object',
        'design' => 'array',
    ];
}
