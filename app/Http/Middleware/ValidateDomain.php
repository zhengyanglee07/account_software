<?php

namespace App\Http\Middleware;
use App\AccountDomain;

use Closure;

class ValidateDomain
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
        $domain = $request->getHost();
        $isBaseDomain = $domain == config('app.domain');
        $isDevEnvironment = app()->environment() === "local";
        if(!$this->isDomainExist($domain, $isBaseDomain) && !$isDevEnvironment && !$isBaseDomain) {
            abort(404);
        }
        return $next($request);
    }

    private function isDomainExist( $domain, $isBaseDomain )
    {
        if($isBaseDomain && Auth()->check()) return true;
        $rootDomain = str_starts_with($domain, "www.")
            ? str_replace("www.", "", $domain)
            : $domain;
        return AccountDomain::ignoreAccountIdScope()->whereDomain($rootDomain)->exists();
    }
}
