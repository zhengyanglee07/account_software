<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Variant extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    public function values()
    {
        return $this->hasMany('App\VariantValue');
    }
}
