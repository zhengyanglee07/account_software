<?php

namespace App\Http\Controllers\API;

use App\Currency;
use App\Http\Controllers\Controller;
use App\Order;
use App\ProcessedTag;
use App\SubscriptionLogs;
use App\Traits\CurrencyConversionTraits;
use App\Traits\TestAccountTrait;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserInfoController extends Controller
{
    use CurrencyConversionTraits, TestAccountTrait;

    /**
     * @return JsonResponse
     */
    public function userAccountBasicInfo(Request $request): JsonResponse
    {
        $users = User
            ::withTrashed()
            ->with(['accounts', 'accounts.subscription', 'accounts.subscriptionPlan'])
            ->orderByDesc('id')
            ->get();

        /**
         * Filter out the production testing accounts from Hypershapes
         */
        if(app()->environment('production')) $users = $users->whereNotIn('currentAccountId', $this->getTestAccountIds());
        $mappedUsers = $users->map(function ($user) use ($request) {
            $firstAccount = $user->accounts()->first();
            $accountSubscription = optional($firstAccount)->subscription;
            $accountSubscriptionPlan = optional($firstAccount)->subscriptionPlan;
            $subscriptionLogs = SubscriptionLogs
                ::latest('recieved_time')
                ->where('subscription_id', optional($accountSubscription)->subscription_id)
                ->first();

            $accountLastPaymentDate = optional($subscriptionLogs)->recieved_time;
            $accountLastPaymentAmount = optional($subscriptionLogs)->amount_paid;

            return [
                'id' => $user->id,
                'email' => $user->email,
                'companyName' => optional($firstAccount)->company ?? '-',
                'signUpDate' => $user->created_at->toDateTimeString() ?? '-',
                'emailVerifiedAt' => $user->email_verified_at, // to check whether user has problem verify email
                'lastLogin' => $user->last_login_at ?? '-',
                'currentPlan' => optional($accountSubscriptionPlan)->plan ?? '-',
                'accountStatus' => optional($firstAccount)->subscription_status ?? '-',
                'lastPaymentMadeAt' => $accountLastPaymentDate ?? '-',
                'lastPaymentAmount' => $accountLastPaymentAmount ?? '-',
                'totalPaymentAmount' => $this->calculateTotalPaymentAmount($accountSubscription),
                'companyRevenue' => $this->calculateCompanyRevenue(optional($firstAccount)->id, $request->conversionRates)
            ];
        });

        return response()->json([
            'status' => 'success',
            'basicInfo' => $mappedUsers,
        ]);
    }

    /**
     * @param $accountSubscription
     * @return int
     */
    private function calculateTotalPaymentAmount($accountSubscription): int
    {
        if (!$accountSubscription) return 0;

        return SubscriptionLogs
            ::where('subscription_id', $accountSubscription->subscription_id)
            ->sum('amount_paid');
    }

    /**
     * @param $accountId
     * @param $conversionRates
     * @return int|mixed
     */
    private function calculateCompanyRevenue($accountId, $conversionRates = null)
    {
        $allOrders = Order
            ::where('account_id', $accountId)
            ->where(function ($query) {
                $query
                    ->where('payment_process_status', 'Success')
                    ->orWhereNull('payment_process_status');
            })
            ->orderBy('order_number', 'desc')
            ->get()
            ->toArray();

        return $this->customGetTotalPrice($allOrders, $accountId, true, $conversionRates);
    }

    /**
     * @return JsonResponse
     */
    public function userAccountQuotaUsage(): JsonResponse
    {
        $domainsPath = storage_path() . "/json/domains.json";
        $disposableEmails = collect(json_decode(file_get_contents($domainsPath), true));
        $users = User::withTrashed()->with(['accounts', 'accounts.accountPlanTotal'])->get()->filter(function ($value) use ($disposableEmails) {
            return !$disposableEmails->contains(substr($value->email, strpos($value->email, "@") + 1));
        });

        $accountQuotaUsage = $users->map(function ($user) {
            $firstAccount = $user->accounts()->first();
            $accountPlanTotal = optional($firstAccount)->accountPlanTotal;

            return [
                'id' => $user->id,
                'people' => optional($accountPlanTotal)->total_people ?? 0,
                'salesFunnel' => optional($accountPlanTotal)->total_funnel ?? 0,
                'landingPage' => optional($accountPlanTotal)->total_landingpage ?? 0,
                'product' => optional($accountPlanTotal)->total_product ?? 0,
                'storage' => optional($accountPlanTotal)->total_image_storage ?? 0,
                'formSubmission' => optional($accountPlanTotal)->total_form ?? 0,
                'domain' => optional($accountPlanTotal)->total_domain ?? 0,
                'users' => optional($accountPlanTotal)->total_user ?? 0,
                'emailSent' => optional($accountPlanTotal)->total_email ?? 0,
                'automations' => optional($accountPlanTotal)->total_automation ?? 0,
                'tags' => $this->getAccountTags(optional($firstAccount)->id)
            ];
        });

        return response()->json([
            'status' => 'success',
            'quotaUsage' => $accountQuotaUsage
        ]);
    }

    /**
     * @param $accountId
     * @return string
     */
    private function getAccountTags($accountId): string
    {
        if (!$accountId) return '-';

        return ProcessedTag::where('account_id', $accountId)->pluck('tagName')->implode(',');
    }

    /**
     * Temp fix for getTotalPrice prob
     *
     * @param $allorders
     * @param $accountId
     * @param bool $number_format
     * @param object $conversionRates
     * @return string
     */
    private function customGetTotalPrice($allorders, $accountId, $number_format = true, $conversionRates = null)
    {
        $totalSales = array_reduce(
            $allorders,
            function ($acc, $order) use ($accountId) {
                return $acc + $this->customConvertCurrency($order['total'], $order['currency'], $accountId, true, true);
            },
            0
        );
        $totalSalesInUSD = $this->convertToUSD($totalSales, $accountId, $conversionRates);
        return $number_format
            ? number_format($totalSalesInUSD, 2)
            : $totalSalesInUSD;
    }

    /**
     * Temp fix for getTotalPrice prob
     *
     * @param $originalPrice
     * @param $currency
     * @param $accountId
     * @param false $isRealTime
     * @param false $toDefault
     * @return float|int
     */
    private function customConvertCurrency($originalPrice, $currency, $accountId, $isRealTime = false, $toDefault = false)
    {
        $defaultCurrency = $this->getCurrencyArray(null, $accountId);

        if (!$defaultCurrency) return 0;

        $exchangeCurrency = $this->getCurrencyArray($currency, $accountId);
        if ($defaultCurrency->currency === $currency) {
            return (float)$originalPrice;
        }
        $exchangeRate = $isRealTime ?  $exchangeCurrency['suggestRate'] : $exchangeCurrency['exchangeRate'];
        return $toDefault ? (float)$originalPrice / (float)$exchangeRate : (float)$originalPrice * (float)$exchangeRate;
    }

    private function getCurrencyArray($currency, $accountId)
    {
        if ($currency === null) {
            return Currency::firstWhere(['account_id' => $accountId, 'isDefault' => '1']);
        }
        return Currency::firstWhere(['account_id' => $accountId, 'currency' => $currency]);
    }

    private function convertToUSD($totalSales, $accountId, $conversionRates)
    {
        $defaultCurrency = $this->getCurrencyArray(null, $accountId);

        if (!$defaultCurrency) return 0;

        $currency = $defaultCurrency->currency;

        if ($currency === 'USD')  return (float)$totalSales;

        return (float)$totalSales / (float)$conversionRates[$currency];
    }

    public function getExchangeRatesBasedOnUSD()
    {
        $today = Carbon::now()->toDateString();
        $req_url = 'https://api.exchangerate.host/latest?base=USD&date=' . $today;
        $response_json = file_get_contents($req_url);
        if (false !== $response_json) {
            try {
                $response = json_decode($response_json);
                if ($response->success === true) {
                    return response()->json($response);
                }
            } catch (Exception $e) {
                // Handle JSON parse error...
            }
        }
    }
}
