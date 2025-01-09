<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductSubscription extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(UsersProduct::class,'users_products_id');
    }

    public function orderSubscriptions()
    {
        return $this->hasMany(OrderSubscription::class,'product_subscription_id');
    }
}
