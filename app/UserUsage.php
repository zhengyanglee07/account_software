<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserUsage extends Model
{
    protected $fillable = [
        'users_id',
        'no_of_people',
        'no_of_email',
        'no_of_funnel',
        'no_of_landingpage',
        'no_of_domain',
        'no_of_users',
    ];
}
