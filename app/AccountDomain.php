<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToAccount;

use Auth;

/**
 * Class AccountDomain
 *
 * @mixin \Eloquent
 */
class AccountDomain extends Model
{
    use BelongsToAccount;

    protected $fillable =
    [
        'account_id',
        'domain',
        'type',
        'type_id',
        'is_subdomain',
        'is_verified',
        'is_affiliate_member_dashboard_domain'
    ];

    public static function getDomainRecord()
    {
        $domain = $_SERVER['HTTP_HOST'];
        if (!empty($_SERVER['HTTP_ORIGIN'])) {
            $parsedOrigin = parse_url($_SERVER['HTTP_ORIGIN']);
            $domain = $parsedOrigin['host'];
        }
        $rootDomain = str_starts_with($domain, "www.")
            ? str_replace("www.", "", $domain)
            : $domain;
        return $rootDomain === "localhost:8000" || $rootDomain === config('app.domain')
            ? null
            : AccountDomain::ignoreAccountIdScope()->whereDomain($rootDomain)->firstOrFail();
    }

    public function scopeOnlineStoreDomain($query)
    {
        return $query->whereType('online-store');
    }

    public static function storeDomain($isMiniStore = false)
    {
        $domain =  AccountDomain::whereType($isMiniStore ? 'mini-store' : 'online-store')->first()->domain ?? null;
        $protocol = app()->environment() == 'local' ? 'http://' : 'https://';
        return $domain ? "{$protocol}{$domain}" : null;
    }

    public static function resetAllOtherStoreDomain($domainId, $type)
    {
        $allOtherDomain = AccountDomain::where('id', '!=', $domainId)->get();
        foreach ($allOtherDomain as $domain) {
            if ($domain->type === 'funnel') continue;
            $domain->update([
                'type' => null,
                'type_id' => null
            ]);
        }
    }

    public static function resetAllOtherAffiliateMemberDashboardDomain($domainId): void
    {
        $allOtherDomain = AccountDomain::where('id', '!=', $domainId)->get();
        foreach ($allOtherDomain as $domain) {
            $domain->update([
                'is_affiliate_member_dashboard_domain' => 0
            ]);
        }
    }

    public static function boot()
    {
        parent::boot();

        // skip these in testing
        if (app()->environment('testing')) {
            return;
        }

        static::created(function ($domain) {
            $account = Auth::user()->currentAccount();
            $accountPlan = $account->accountPlanTotal;
            $accountPlan->total_domain = $account->domains->where('is_subdomain', false)->count();
            $accountPlan->save();
        });

        static::deleted(function ($domain) {
            $account = Auth::user()->currentAccount();
            $accountPlan = $account->accountPlanTotal;
            $accountPlan->total_domain = $account->domains->where('is_subdomain', false)->count();
            $accountPlan->save();
        });
    }
}
