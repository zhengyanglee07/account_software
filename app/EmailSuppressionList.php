<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailSuppressionList extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'email_address',
        'reason',
        'updated_at',
    ];
}
