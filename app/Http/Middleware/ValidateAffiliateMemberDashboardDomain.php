<?php

namespace App\Http\Middleware;

use App\AccountDomain;
use Closure;

class ValidateAffiliateMemberDashboardDomain
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $domain = $request->getHost();

        $isBaseDomain = $domain === '127.0.0.1' || $domain === config('app.domain');

        if (!$isBaseDomain && !$this->isAffiliateMemberDashboardDomain($domain)) {
            abort(404);
        }
        return $next($request);
    }

    private function isAffiliateMemberDashboardDomain($domain): bool
    {
        return AccountDomain
            ::where([
                'domain' => $domain,
                'is_verified' => 1,
                'is_affiliate_member_dashboard_domain' => 1,
            ])
            ->exists();
    }
}
