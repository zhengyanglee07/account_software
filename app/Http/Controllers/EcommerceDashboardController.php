<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Account;
use App\Currency;
use App\EcommerceHeaderFooter;
use App\EcommerceNavBar;
use App\UsersProduct;
use Auth;
use App\EcommercePreferences;
use App\AccountDomain;
use App\Traits\PublishedPageTrait;
use App\Traits\CurrencyConversionTraits;
use Inertia\Inertia;


class EcommerceDashboardController extends Controller
{
    use PublishedPageTrait, CurrencyConversionTraits;

    private function user()
    {
        return Auth::guard('ecommerceUsers')->user();
    }

    public function showDashboard()
    {

        $publishPageBaseData = $this->getPublishedPageBaseData();
        $pageName = "Dashboard Page";
        $currentURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $accountId = $this->getAccountId();
        $company = Account::find($accountId);

        $affiliateDomain = AccountDomain
            ::where([
                'account_id' => $accountId,
                'is_affiliate_member_dashboard_domain' => 1
            ])
            ->first();
        $companyDetails = [
            'company_name' => $company['company'],
            'product_url' => $currentURL,
            'affiliate_domain' => $affiliateDomain->domain ?? null
        ];
        // $headerFooterMiscSettings = $this->getHeaderFooterSection();
        $customerInfo = $this->user()->processedContact;
        $customerInfo->addressBook = $this->user()->addressBook[0];
        $prefix = $this->getCurrencyArray(null)->prefix;
        $totalOrder = $this->getTotalPrice($customerInfo->orders->toArray(), false);
        // dd($headerFooterMiscSettings, $this->user()->processedContact);
        return Inertia::render(
            'customer-account/pages/MainDashboard',
            array_merge(
                $publishPageBaseData,
                [
                    'customerInfo' => $customerInfo,
                    'totalOrder' => $totalOrder,
                    'prefix' => $prefix ?? null,
                    'pageName' => $pageName ?? null,
                ]
            )
            // array_merge($headerFooterMiscSettings, $companyDetails)
        );
    }
}
