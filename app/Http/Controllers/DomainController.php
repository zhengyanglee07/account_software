<?php

namespace App\Http\Controllers;

use Auth;

use App\Account;
use App\funnel;
use App\Currency;
use App\PaymentAPI;
use App\Page;
use App\UsersProduct;
use App\AccountDomain;
use App\MiniStore;
use App\MiniStoreTheme;
use App\ProductReview;
use App\EcommerceNavBar;
use Illuminate\Http\Request;


use App\EcommercePreferences;
use App\ProductReviewSetting;
use App\EcommerceHeaderFooter;
use App\Traits\CurrencyConversionTraits;

use App\EcommerceVisitor;
use App\EcommerceTrackingLog;
use App\EcommerceAbandonedCart;
use App\EcommercePage;

use App\Traits\PublishedPageTrait;

use Inertia\Inertia;

class DomainController extends Controller
{
    use PublishedPageTrait;

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function showSettings()
    {
        $account = Auth::user()->currentAccount();
        $domains = AccountDomain::all();
        $subdomainId = $domains->firstWhere('is_subdomain', 1)->id;
        $allFunnels = funnel::select('id', 'funnel_name')->get();
        $enabledSalesChannels = $account->saleChannelsType();
        $planDomainQuota = $account->permissionMaxValue('add-domain');

        return Inertia::render('setting/pages/DomainSettings', compact(
            'domains',
            'subdomainId',
            'allFunnels',
            'enabledSalesChannels',
            'planDomainQuota',
        ));
    }

    public function checkDomainAvailability(Request $request)
    {
        $isSkipPermissionChecker = $request->isSkipPermissionChecker ?? false;

        $isExisted = AccountDomain::ignoreAccountIdScope()->when(
            $isSkipPermissionChecker,
            function ($query) {
                return $query->where('account_id', '!=', Auth::user()->currentAccountId);
            }
        )
            ->where('domain', $request->domainName)
            ->exists();

        return response()->json([
            'isExisted' => $isExisted
        ]);
    }

    public function save(Request $request)
    {
        AccountDomain::create([
            'domain' => $request->newDomainName,
            'is_verified' => app()->environment() === 'local' ? 1 : 0,
        ]);

        return response()->json([
            'message' => "Custom domain added successfully",
            'updatedRecords' => AccountDomain::all(),
        ]);
    }

    public function verifyCustomDomain(AccountDomain $domain)
    {
        if (app()->environment() !== 'local') {
            $customDomainIp = exec("dig +short {$domain->domain}");

            if ($customDomainIp !== config('app.wildcard_ip')) {
                return response()->json([
                    'status' => 'Failed',
                    'message' => 'The domain is not connected to our server'
                ], 500);
            }

            $this->triggerOnDemandSSL($domain->domain);
        }

        $domain->update([
            'is_verified' => 1
        ]);

        return response()->json([
            'status' => 'Success',
            'domain' => $domain->domain,
            'updatedRecords' => AccountDomain::all(),
        ]);
    }

    public function triggerOnDemandSSL($domain = null)
    {
        if (!$domain) {
            $domain = AccountDomain::firstWhere('is_subdomain', 1)->domain;
        }

        exec("curl -I https://{$domain}");

        return response()->json([
            'domain' => $domain,
        ]);
    }

    public function update(AccountDomain $domain, Request $request)
    {
        $salesChannel = $request->type;
        $isAffiliateMemberDashboardDomain = $request->is_affiliate_member_dashboard_domain;

        $domain->update([
            'domain' => $request->newDomainName,
            'type' => $salesChannel,
            'type_id' => $request->type_id,
            'is_affiliate_member_dashboard_domain' => $isAffiliateMemberDashboardDomain ?? false,
        ]);

        if ($salesChannel == 'online-store' || $salesChannel == 'mini-store') {
            $defaultHomeId = AccountDomain::whereType('online-store')->whereNotNull('type_id')->first()->type_id ?? null;
            $domain->update([
                'type_id' => $salesChannel == 'mini-store' ? null : $defaultHomeId
            ]);
            AccountDomain::resetAllOtherStoreDomain($domain->id, $salesChannel);
        }

        if ($isAffiliateMemberDashboardDomain) {
            AccountDomain::resetAllOtherAffiliateMemberDashboardDomain($domain->id);
        }

        return response()->json([
            'message' => "Successfully updated {$domain->domain}",
            'updatedRecords' => AccountDomain::all(),
        ]);
    }

    public function delete(AccountDomain $domain)
    {
        try {
            $domain->delete();
            return response()->json([
                "status" => "Success",
                'message' => "Domain {$domain->domain} was successfully deleted",
                "records" => AccountDomain::all(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "Fail to delete domain",
                "message" => "Please contact out support",
            ]);
        }
    }

    // TODO remove
    public function domainInfo()
    {
        return AccountDomain::getDomainRecord();
    }

    public function isPreviewMode()
    {
        return config('app.domain') == $_SERVER['HTTP_HOST'];
    }

    public function getAccountSubdomain()
    {
        $subdomain = AccountDomain::where([
            'account_id' => Auth::user()->currentAccountId,
            'is_subdomain' => 1
        ])->first()->domain;

        return response()->json([
            'subdomain' => $subdomain
        ]);
    }

    public function isDomainExisted(Request $request)
    {
        if (AccountDomain::ignoreAccountIdScope()->whereDomain($request->query('domain'))->doesntExist()) {
            abort(404);
        }
    }

    private function isUsedByFunnel()
    {
        return funnel::where([
            'domain_name' => $_SERVER['HTTP_HOST'],
        ])->exists();
    }

    public function getDefaultPageRecord()
    {
        $domain = $this->domainInfo();

        switch ($domain->type) {
            case 'store':
                if (!$domain->type_id) abort(404);
                $landing = Page::where('is_published', true)->findOrFail(
                    $this->domainInfo()->type_id
                );
                return $this->renderLandingPage($landing, true);
                break;
            case 'funnel':
                if (!$domain->type_id) abort(404);
                $funnel = funnel::findOrFail($domain->type_id);
                $landing = $funnel->landingpages()->where('is_published', true)->orderBy('index')->firstOrFail();
                return $this->renderLandingPage($landing, true);
                break;
            case 'mini-store':
                $theme = MiniStoreTheme::firstOrFail();
                $miniStoreSettings = MiniStore::where([
                    'account_id' => $domain->account_id,
                ])->firstOrFail();
                $miniStoreSettings['element'] = $theme->landing_page;
                $miniStoreSettings['header'] = $theme->header;
                return $this->renderEcommercePage($miniStoreSettings, $miniStoreSettings->account_id, true);
                break;
        }
    }

    public function getPageRecord()
    {
        $isPreview = $this->isPreviewMode();
        $accountId = $isPreview
            ? Auth::user()->currentAccountId
            : $this->domainInfo()->account_id;
        $landing = Page::where([
            'account_id' => $accountId,
            'path' => $this->request->path(),
        ])->when(
            !$isPreview,
            function ($query) {
                return $query->where('is_published', true);
            }
        )->firstOrFail();

        return $this->renderLandingPage($landing, false);
    }

    public function renderLandingPage($landingSettings, $isDefaultPage)
    {
        $pageName = $landingSettings->id;
        $accountId = $landingSettings->account_id;
        $isOnlineStorePage = $landingSettings->is_landing == false;

        if ($isOnlineStorePage) {
            return $this->renderEcommercePage($landingSettings, $accountId);
        }

        if (!$isDefaultPage && !$this->isUsedByFunnel()) {
            abort(404);
        }

        $matchAccountId = [
            'account_id' => $accountId
        ];

        $status = "Publish";
        $pageType = "landing";
        $allProducts = UsersProduct::where($matchAccountId)->get();
        $allLandingPages = Page::where($matchAccountId)->get();
        $funnel = funnel::where($matchAccountId)->findOrFail($landingSettings->funnel_id);
        $hasAffiliateBadge = $funnel->has_affiliate_badge ?? true;

        $matchFunnel = [
            'account_id' => $accountId,
            'funnel_id' => $funnel->id,
            'is_published' => true,
        ];

        $funnelCurrency = $funnel->currency;
        $firstLandingId = Page::where($matchFunnel)->firstWhere('index', '0')->id;
        $latestLandingId = Page::where($matchFunnel)->orderBy('index', 'DESC')->first()->id;
        $currencyDetails = $this->getCurrencyArray($funnelCurrency);
        $stripePaymentAPI = PaymentAPI::where($matchAccountId)->firstWhere('payment_methods', 'Stripe');

        $isCustomerLoggedIn = Auth::guard('ecommerceUsers')->check();

        return view('previewpage', compact(
            'status',
            'landingSettings',
            'allProducts',
            'funnel',
            'funnelCurrency',
            'hasAffiliateBadge',
            'allLandingPages',
            'stripePaymentAPI',
            'firstLandingId',
            'latestLandingId',
            'pageType',
            'currencyDetails',
            'pageName',
            'isCustomerLoggedIn'
        ));
    }

    public function renderEcommercePage($landingSettings, $accountId, $isMiniStore = false)
    {
        $dataArray = $this->getHeaderFooterSection();
        $dataArray['pageName'] = $landingSettings->id;
        $dataArray['isCustomerLoggedIn'] = Auth::guard('ecommerceUsers')->check();

        if ($isMiniStore) {
            $dataArray['landingSettings'] = $landingSettings;
        } else {
            $dataArray['landingSettings'] = Page::where([
                'is_landing' => false,
                'account_id' => $accountId,
                'id' => $landingSettings->id
            ])->first();
        }
        return view('ecommercePreviewPage', $dataArray);
    }

    public function updateOnlineStoreHome($pageId)
    {
        $domain = AccountDomain::whereType('online-store')->first();
        $domain->update([
            'type' => 'online-store',
            'type_id' => $pageId
        ]);

        return response()->json([
            'updatedRecords' => $domain
        ]);
    }

    public function updateSubdomainToBeMiniStoreDomain()
    {
        $subdomain = AccountDomain::where([
            'account_id' => Auth::user()->currentAccountId,
            'is_subdomain' => true
        ])->first();

        $subdomain->update([
            'type' => 'mini-store'
        ]);
    }
    public function getRandomId($table, $type)
    {
        $condition = true;
        do {
            return $randomId = random_int(100000000001, 999999999999);
            $condition = DB::table($table)->where($type, $randomId)->exists();
        } while ($condition);
    }
}
