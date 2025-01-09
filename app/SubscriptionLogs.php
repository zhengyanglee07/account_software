<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubscriptionLogs extends Model
{
    protected $fillable = [
        'subscription_id',
		'status',
		'amount_paid',
        'recieved_time',
        'invoice_pdf_url',
    ];
}
