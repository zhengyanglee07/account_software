<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AffiliateMemberSetting
 * @package App
 *
 * @property int $auto_approve_affiliate
 * @property int $auto_approve_commission
 * @property float $auto_approval_period
 * @property int $auto_create_account_on_customer_sign_up
 * @property float $minimal_payout
 * @property int $enable_lifetime_commission
 * @property float $cookie_expiration_time
 *
 * @mixin \Eloquent
 */
class AffiliateMemberSetting extends Model
{
    protected $guarded = ['id'];

    /**
     * Find one setting by accountId or create a default
     *
     * @param $accountId
     * @return \App\AffiliateMemberSetting|\Illuminate\Database\Eloquent\Model
     */
    public static function findOneOrCreateDefault($accountId)
    {
        return self::firstOrCreate(
            ['account_id' => $accountId],
            [
                'auto_approve_affiliate' => 1,
                'auto_approve_commission' => 1,
                'auto_approval_period' => 30,
                'auto_create_account_on_customer_sign_up' => 1,
                'minimal_payout' => 100,
                'enable_lifetime_commission' => 0,
                'cookie_expiration_time' => 90
            ]
        );
    }
}
