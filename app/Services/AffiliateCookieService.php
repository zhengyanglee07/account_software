<?php

namespace App\Services;

use App\AffiliateMemberAccount;
use App\AffiliateMemberReferralClickLog;
use App\AffiliateMemberSetting;
use App\Traits\ReferralCampaignTrait;

class AffiliateCookieService
{
    use ReferralCampaignTrait;

    public const REFERRAL_TOKEN_NAME = 'x-refer-by-affiliate-member';

    public static function hasReferToken()
    {
        return !empty(request()->header(self::REFERRAL_TOKEN_NAME));
    }

    public static function getReferToken()
    {
        if (!empty(request()->header(self::REFERRAL_TOKEN_NAME))) {
            return request()->header(self::REFERRAL_TOKEN_NAME);
        }
    }

    public function get($accountId)
    {
        $request =  request();
        if (!$request->query('aff')) return;

        parse_str($request->getQueryString(), $referral);
        $identifierCode = $referral['aff'];
        $id = $this->decodeReferralCode($identifierCode, $accountId);
        $affiliateMemberAccount = AffiliateMemberAccount::find($id);

        if (!$affiliateMemberAccount) return;

        AffiliateMemberReferralClickLog::create([
            'referral_identifier' => $affiliateMemberAccount->referral_identifier,
        ]);
        return [
            'name' => 'refer_by_affiliate_member',
            'value' => 'ref=' . urlencode($affiliateMemberAccount->referral_identifier),
            'expire' =>  $this->getCookiePeriod($accountId),
        ];
    }

    private  function getCookiePeriod($accountId)
    {
        $defaultExpirationTime = 86400;

        if (!$accountId) {
            return $defaultExpirationTime;
        }

        $expirationTime = AffiliateMemberSetting::findOneOrCreateDefault($accountId)->cookie_expiration_time;

        if ($expirationTime <= 0) {
            return $defaultExpirationTime;
        }

        // cookie_expiration_time is in days
        return $expirationTime;
    }
}
