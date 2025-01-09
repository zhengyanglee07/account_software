<?php

namespace App;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Eloquent;

class DelyvaDeliveryOrderDetail extends Model
{
    // use HasFactory;

    public function delyvaOrders(): HasMany
    {
        return $this->belongsTo(DelyvaOrder::class,'delyva_delivery_order_id');
    }

    protected $table = 'delyva_delivery_order_details';

    protected $fillable = [
        'delyva_delivery_order_id',
        'serviceCode',
        'consignmentNo',
        'invoiceId',
        'itemType',
        'statusCode',
        'status',
        'failed_reason',
        'service_name',
        'service_company_name',
        'schedule_at',
        'company_id',
        'total_fee_amount',
        'total_fee_currency',
    ];

}