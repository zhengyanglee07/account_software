<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductRecommendation extends Model
{
    protected $guarded = [];
    
    public function accounts()
    {
        return $this->belongsToMany('App\Account');
    }
}
