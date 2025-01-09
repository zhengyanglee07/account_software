<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];
    protected $table = 'category';
    protected $with = ['products'];

    public function products(){
        return $this->belongsToMany(UsersProduct::class, 'product_category',
        'category_id', 'product_id');
    }

}
