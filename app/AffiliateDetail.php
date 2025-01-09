<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AffiliateDetail extends Model
{
	use SoftDeletes;

	protected $fillable = [
		'affiliate_userid',
		'affiliate_unique_link',
		'total_click_for_link',
		'current_paid_account',
		'total_referrals',
		'current_trial_account',
		'total_commission',
		'total_pending_commission',
		'total_withdrawal',
		'current_balance',
		'commission_rate',

    ];
}
