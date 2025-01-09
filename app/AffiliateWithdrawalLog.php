<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AffiliateWithdrawalLog extends Model
{
	protected $fillable = [
	   'transaction_id',
	   'affiliate_id',
	   'withdraw_amount',
	   'paypal_email',
	   'withdraw_date'
    ];
}
