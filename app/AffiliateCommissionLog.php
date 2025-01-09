<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AffiliateCommissionLog extends Model
{
	use SoftDeletes;
    protected $guarded = ['id'];
    protected $date = ['credited_date'];
    public function affiliateReferral(){
        return $this->belongsTo(AffiliateReferralLog::class,'referral_id','id');
    }
}
