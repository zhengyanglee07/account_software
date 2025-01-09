<?php

namespace App;

use App\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 

class Badges extends Model
{
    use SoftDeletes;
    use BelongsToAccount;
    protected $guarded = [];

    public function products()
    {
        return $this->belongsToMany('App\UsersProduct');
    }
}
