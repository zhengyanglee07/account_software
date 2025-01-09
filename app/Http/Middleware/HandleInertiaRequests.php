<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

use Auth;
use App\Account;
use App\AccountDomain;
use App\App;
use App\Currency;
use App\funnel;
use App\Traits\AuthAccountTrait;

class HandleInertiaRequests extends Middleware
{
    use AuthAccountTrait;
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    public function rootView(Request $request)
    {
        $path = $request->path();
        $isBuilder = strpos($path, 'editor') !== false;
        $isBuilderPreview = strpos($path, 'preview') !== false;
        $isAdminPanel = $request->getHost() === config('app.domain');

        return (!$isAdminPanel || $isBuilder || $isBuilderPreview)
            ? 'builder'
            : 'app';
    }

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        $user = Auth()->user();
        $accountId = $this->getCurrentAccountId();
        $isAuthenticated = Auth()->check();
        $isAffiliateMemberAuthenticated = Auth::guard('affiliateMember')->user()->name ?? false;
        $account = Account::find($accountId);
        return array_merge(parent::share($request), [
            'permissionData' => $request['permissionData'],
            'isLocalEnv' => app()->environment() === 'local',
            'username' => (Auth::user()->firstName ?? '') . ' ' . ($user->lastName ?? ''),
            'timezone' => $isAuthenticated
                ? ($user->currentAccountId ? $user->currentAccount()->timeZone ?? 'Asia/Kuala_Lumpur' : '')
                : 'Asia/Kuala_Lumpur',
            'funnelDomain' =>  $isAuthenticated ? funnel::ignoreAccountIdScope()->where([
                'account_id' => $accountId
            ])->get() : null,
            'onlineStoreDomain' => $isAuthenticated
                ? AccountDomain::storeDomain()
                : null,
            'miniStoreDomain' => $isAuthenticated
                ? AccountDomain::storeDomain(true)
                : null,
            'enabledSalesChannels' => $isAuthenticated
                ? Account::saleChannelsType()
                : null,
            'currencyDetails' => $isAuthenticated
                ? Currency::currencyDetails()
                : null,
            'totalOpenOrder' => $isAuthenticated
                ? $account?->orders->where('additional_status', 'Open')->count()
                : 0,
            'totalPendingAffiliateMember' => $isAuthenticated
                ? Account::pendingAffiliateMember()->count()
                : 0,
            'totalPendingAffiliateCommissions' => $isAuthenticated
                ? Account::pendingAffiliateCommissions()->count()
                : 0,
            'totalPendingAffiliatePayouts' => $isAuthenticated
                ? Account::pendingAffiliatePayouts()->count()
                : 0,
            'affiliateMemberName' => $isAffiliateMemberAuthenticated
                ? Auth::guard('affiliateMember')->user()->name()
                : null,
            'affiliateMemberPendingPayouts' => $isAffiliateMemberAuthenticated
                ?  Auth::guard('affiliateMember')->user()->participant->pendingPayouts()->count()
                : 0,
            'ipInfo' => ip_info(),
            'account' => $account,
            'enabledApps' => $account?->apps,
        ]);
    }
}
