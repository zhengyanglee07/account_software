<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class App extends Model
{
    protected $guarded = ['id'];

    protected $table = 'apps';

    public function accounts()
    {
        return $this->belongsToMany('App\Account');
    }
}
