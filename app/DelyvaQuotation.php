<?php

namespace App;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class DelyvaQuotation extends Model
{
    // use HasFactory;

    protected $with = ['delyvaDeliveryOrder'];

    public function delyvaQuotations(){

        return $this->belongsTo(Order::class, 'order_id');
    }

    public function delyvaDeliveryOrder(){
        return $this->hasMany(DelyvaDeliveryOrder::class);
    }

    protected $table = 'delyva_quotations';

    protected $fillable = [
        'order_id',
        'scheduled_at',
        'service_name',
        'service_code',
        'type',
        'total_fee_amount',
        'total_fee_currency',
        'service_company_name',
        'service_detail',
    ];

}