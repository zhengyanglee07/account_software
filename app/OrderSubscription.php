<?php

namespace App;

use App\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\Model;

class OrderSubscription extends Model
{
    use BelongsToAccount;

    protected $guarded = [];

    public function processedContact()
    {
        return $this->belongsTo(ProcessedContact::class,'processed_contact_id');
    }

    public function productSubscription()
    {
        return $this->belongsTo(ProductSubscription::class,'product_subscription_id')->withTrashed();
    }

    public function orders()
    {
        return $this->hasMany(Order::class)->where(function($query)
            {
                $query->where('payment_process_status','Success')->orWhereNull('payment_process_status');
            }
        )
        ->orderBy('created_at','DESC');
    }

    public function account()
    {
        return $this->belongsTo(Account::class,'account_id');
    }
}

