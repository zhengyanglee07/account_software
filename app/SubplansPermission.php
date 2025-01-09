<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubplansPermission extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'plan_id',
        'permission_id',
        'max_value'
    ];
}
