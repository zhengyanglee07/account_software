<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Cookie;
use Hashids\Hashids;
use Closure;
use App\AffiliateUser;
use App\AffiliateClickLog;

class AffiliateTracking
{
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


        if ($request->has('affiliate-id')) {
            $code = $request->get('affiliate-id');
            $len = strlen($code);
            $hashids = new Hashids('affiliate.hypershapes.com', $len, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
            $affId =  $hashids->decodeHex($code);
            $domain = app()->environment('local') ? '' : '.hypershapes.com';
            $aff = AffiliateUser::find($affId);
            if ($aff) {
                if (request()->cookie('refer_by') !== $aff?->affiliate_unique_id) {
                    AffiliateClickLog::create([
                        'affiliate_unique_id' => $aff?->affiliate_unique_id
                    ]);
                }
                $response->withCookie(cookie('refer_by', $aff?->affiliate_unique_id, 86400, null, $domain, null, false));
            }
        }
        return $response;
    }
}
