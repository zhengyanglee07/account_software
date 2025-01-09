<?php

namespace App;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\hasOne;
use Eloquent;

class DelyvaDeliveryOrder extends Model
{
    // use HasFactory;
    protected $with = ['delyvaDeliveryOrderDetail'];

    public function delyvaQuotations(){

        return $this->belongsTo(DelyvaQuotation::class, 'delyva_quotation_id');
    }

    public function delyvaDeliveryOrderDetail(){
        return $this->hasOne(DelyvaDeliveryOrderDetail::class);
    }

    protected $table = 'delyva_delivery_orders';

    protected $fillable = [
        'delyva_quotation_id',
        'delyva_order_id',
        'order_number',
    ];

}