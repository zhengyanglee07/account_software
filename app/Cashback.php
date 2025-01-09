<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cashback extends Model
{
    protected $guarded = [];
    public function segments()
    {
        return $this->belongsToMany('App\Segment', 'people_cashbacks', 'cashback_id', 'segment_id')->distinct();
    }

    public function saleChannels() {
        return $this->belongsToMany(SaleChannel::class, 'cashback_sale_channels', 'cashback_id', 'sale_channel_id');
    }
}
