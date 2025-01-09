<?php

namespace App\Traits;

use Auth;
use Carbon\Carbon;
use App\Account;
use App\AccountDomain;
use Illuminate\Support\Facades\Log;

trait AuthAccountTrait
{
    /**
     * Simple helper method to obtain accountId of
     * authenticated user (admin panel) or store/funnel's owner (publish mode)
     *
     * @param bool $isIncludeDomain
     * @return int|array
     */
    public function getCurrentAccountId($isIncludeDomain = false)
    {
        if (request()->path() === 'login') return;
        $isAuthenticated = isset(Auth::user()->currentAccountId);

        $domain = null;
        if (!$isAuthenticated) {
            $domain = AccountDomain::getDomainRecord();
        } else if ($isIncludeDomain) {
            $domain = AccountDomain::where('account_id', Auth::user()->currentAccountId)
                ->whereNotNull('type')->first();
        }

        $accountId = $isAuthenticated
            ? Auth::user()->currentAccountId
            : ($domain ? $domain->account_id : null);

        if ($isIncludeDomain) {
            return [
                'domain' => $domain,
                'accountId' => $accountId,
            ];
        }

        return $accountId;
    }

    public function getTerminateCycle()
    {
        return Account::find($this->getCurrentAccountId())->terminate_cycle;
    }

    public function convertDatetimeToSelectedTimezone($datetime, $domain = null)
    {
        $accountTimeZone = Account::find($this->getCurrentAccountId($domain))->timeZone;
        $datetime = new Carbon($datetime);
        return $datetime->timezone($accountTimeZone)->isoFormat('YYYY-MM-DD\\ H:mm:ss');
    }

    /**
     * Generate unique reference_key for a table column
     * @param string $table
     * @param string $column
     * @return integer 
     */
    public function getRandomId($table, $column = 'reference_key')
    {
        $condition = true;
        do {
            return $randomId = random_int(100000000001, 999999999999);
            $condition = DB::table($table)->where($column, $randomId)->exists();
        } while ($condition);
    }
}
