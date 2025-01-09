<?php

namespace App\Http\Middleware;

use App\AccountDomain;
use App\ReferralCampaignClickLog;
use App\Traits\ReferralCampaignTrait;
use Illuminate\Support\Str;

use Closure;

class ReferralCampaignStoreLastCookie
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

        if (!$request->query('invite') && !$request->query('is_referral')) {
            return $response;
        }

        $accountDomain = AccountDomain::where('domain', $request->getHost())->first();
        if($accountDomain) {
            $referralCode = null;
            $selectedCampaign = null;
            $referralCampaigns = $this->getReferralCampaigns($accountDomain->account_id)->where('status', true);
            if($request->query('invite')){
                $referralCode = Str::after($request->getQueryString(), 'invite=');
            }
            if($request->query('is_referral')){
                $referralCode = Str::after($request->getQueryString(), 'is_referral=');
            }
            $referralCampaigns->map(function($campaign) use($referralCode, $request, &$selectedCampaign){
                $processedContactId = $this->decodeReferralCode($referralCode, $campaign->reference_key);
                if($processedContactId!==''){
                    $selectedCampaign = $campaign;
                    if($request->query('invite')){
                        ReferralCampaignClickLog::create([
                            'processed_contact_id' => $processedContactId,
                            'referral_campaign_id' => $campaign->id,
                        ]);
                    }
                }
                return $campaign;
            });
        }

        if ($request->query('invite')) {
            return $response->withCookie(
                cookie(
                    'referral',
                    $request->getQueryString(),
                    (3*30*24*60) // 90 days
                )
            );
        }

        if ($request->query('is_referral')) {
            return $response->withCookie(
                cookie(
                    'funnel#user#'.$selectedCampaign->reference_key,
                    $referralCode,
                    (3*30*24*60) // 90 days
                )
            );
        }

    }
}
