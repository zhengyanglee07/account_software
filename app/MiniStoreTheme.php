<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MiniStoreTheme extends Model
{
    public function accounts()
    {
        return $this->belongsToMany('App\Account');
    }
}
