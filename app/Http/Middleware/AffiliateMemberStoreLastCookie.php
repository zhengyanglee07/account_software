<?php

namespace App\Http\Middleware;

use App\AccountDomain;
use App\AffiliateMemberSetting;
use App\AffiliateMemberAccount;
use App\AffiliateMemberReferralClickLog;
use Closure;
use App\Traits\ReferralCampaignTrait;

class AffiliateMemberStoreLastCookie
{
    use ReferralCampaignTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (!$request->query('aff')) {
            return $response;
        }
        parse_str($request->getQueryString(), $referral);
        $accountDomain = AccountDomain::where('domain', $request->getHost())->first();
        $identifierCode = $referral['aff'];
        $id = $this->decodeReferralCode($identifierCode, $accountDomain->account_id);
        $affiliateMemberAccount = AffiliateMemberAccount::find($id);

        if(!$affiliateMemberAccount){
            return $response;
        }

        AffiliateMemberReferralClickLog::create([
            'referral_identifier' =>$affiliateMemberAccount->referral_identifier,
        ]);
        return $response->withCookie(
            cookie(
                'refer_by_affiliate_member',
                'ref='.urlencode($affiliateMemberAccount->referral_identifier),
                $this->getCookiePeriod($request->getHost())
            )
        );
    }

    private function getCookiePeriod($domain)
    {
        $accountId = AccountDomain
                ::where('domain', $domain)
                ->first()
                ->account_id ?? null;
        $defaultExpirationTime = 86400;

        if (!$accountId) {
            return $defaultExpirationTime;
        }

        $expirationTime = AffiliateMemberSetting::findOneOrCreateDefault($accountId)->cookie_expiration_time;

        if ($expirationTime <= 0) {
            return $defaultExpirationTime;
        }

        // cookie_expiration_time is in days, cookie needs minutes
        return $expirationTime * 24 * 60;
    }
}
