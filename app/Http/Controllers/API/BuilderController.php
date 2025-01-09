<?php

namespace App\Http\Controllers\API;

use App\Account;
use App\AccountDomain;
use App\AffiliateUser;
use App\AllTemplate;
use App\Currency;
use App\EcommerceHeaderFooter;
use App\EcommerceNavBar;
use App\EcommercePreferences;
use App\funnel;
use App\Page;
use App\Popup;
use App\User;
use App\UsersProduct;
use App\UserTemplate;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AffiliateCookieService;
use App\Traits\UsersProductTrait;
use Auth;
use Illuminate\Support\Str;

class BuilderController extends Controller
{
    use UsersProductTrait;

    public function transformData()
    {
        $activeAccounts = User::whereYear("last_login_at", ">=", "2023")->pluck("currentAccountId");
        $pages = AllTemplate::whereNotIn("type", ["theme", "email"])->select("id", "name", "element", "design")->get();
        return response()->json($pages);
    }

    public function transform(Request $request)
    {
        $arr = [];
        foreach ($request->data as $data) {
            array_push($arr, $data["id"]);
            $page = AllTemplate::find($data['id']);
            $page->element = $data["element"];
            $page->design = $data["design"];
            $page->save();
        }
        return response()->json($arr);
    }

    public function index(Request $request)
    {
        $accountId = $request->user()->currentAccountId;
        $pages = Page::whereAccountId($accountId)->select(
            'id',
            'name',
            'is_landing',
            'reference_key',
        )->get()->map(function ($page) {
            $page->type = $page->is_landing ? 'landing' : 'page';
            return $page;
        });
        $headerFooters = EcommerceHeaderFooter::whereAccountId($accountId)->select(
            'id',
            'name',
            'is_header',
            'reference_key',
        )->get()->map(function ($headerFooter) {
            $headerFooter->type = $headerFooter->is_header ? 'header' : 'footer';
            return $headerFooter;
        });

        return response()->json([
            'builderPaths' => $pages->merge($headerFooters),
        ]);
    }

    /**
     * Return the db record of builder settings
     * 
     * @param  string $builderType
     * @param  int $refkey
     */
    private function pageDesign(Request $request, $builderType, $refKey)
    {
        $accountId = $request->user()->currentAccountId;

        switch ($builderType) {
            case "header":
            case "footer":
                $builderSettings = EcommerceHeaderFooter::where([
                    'account_id' => $accountId,
                    'is_header' => $builderType === 'header',
                    'reference_key' => $refKey,
                ])->firstOrFail();
                break;
            case "page":
            case "landing":
                $builderSettings = Page::where([
                    'account_id' => $accountId,
                    'reference_key' => $refKey,
                ])->firstOrFail();
                break;
            case "userTemplate":
                $builderSettings = UserTemplate::findOrFail($refKey);
                break;
            case 'template':
                $builderSettings = AllTemplate::findOrFail($refKey);
                break;
            case 'popup':
                $builderSettings = Popup::where('reference_key', $refKey)->firstOrFail();
                break;
            default:
                $builderSettings = null;
        }

        return $builderSettings;
    }

    /**
     * Edit the builder settings
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $builderType, $builderRefKey)
    {
        $builderSettings = $this->pageDesign($request, $builderType, $builderRefKey);
        $accountId = $request->user()->currentAccountId;
        $subdomain = AccountDomain::where('is_subdomain', true)->first()->domain;
        $popups = Popup::where([
            'account_id' => $accountId,
        ])->select(
            'id',
            'name',
            'design',
            'configurations',
            'reference_key',
        )->latest()->get();

        switch ($builderType) {
            case 'landing':
                $funnelSettings = funnel::findOrFail($builderSettings->funnel_id);
                $domain = AccountDomain::where([
                    'account_id' => $accountId,
                    'is_verified' => true,
                    'type' => 'funnel',
                    'type_id' => $funnelSettings?->id,
                ])->first();
                $firstPublishedPage = $funnelSettings->landingpages()->where('is_published', true)->orderBy('index')->first();
                $isDefaultPage = $domain && $firstPublishedPage?->id === $builderSettings->id;
                $publishDomain = $isDefaultPage
                    ? $domain->domain
                    : $funnelSettings?->domain_name;
                $landingDesign = $builderSettings;
                $allSections = $funnelSettings->landingpages()->orderBy('index')->select(
                    'name',
                    'is_published',
                    'index',
                    'path',
                    'reference_key'
                )->get();
                break;
            case "page":
            case "header":
            case "footer":
                $domain = AccountDomain::where([
                    'account_id' => $accountId,
                    'is_verified' => true,
                    'type' => 'online-store',
                ])->first();
                $publishDomain = $domain->domain;
                $isDefaultPage = $domain->type_id === ($builderSettings->id ?? null);
                $storeDetails = Account::select([
                    'favicon',
                    'company_logo',
                    'store_name',
                ])->find($accountId);
                $menuListArray = EcommerceNavBar::select(
                    'id',
                    'title',
                    'menu_items'
                )->whereAccountId($accountId)->get();

                $pageDesign = Page::find($domain->type_id) ?? null;
                $headerDesign = EcommerceHeaderFooter::ofActive()->select('element', 'design', 'reference_key')->ofType(true, $accountId)->first();
                $footerDesign = EcommerceHeaderFooter::ofActive()->select('element', 'design', 'reference_key')->ofType(false, $accountId)->first();

                if ($builderType === 'page') {
                    $pageDesign = $builderSettings;
                    $allSections = Page::where([
                        'account_id' => $accountId,
                        'is_landing' => false,
                    ])->select(
                        'name',
                        'reference_key',
                    )->latest()->get();
                }

                if ($builderType === 'header') {
                    $headerDesign = $builderSettings;
                    $allSections = EcommerceHeaderFooter::where([
                        'account_id' => $accountId,
                        'is_header' => true,
                    ])->select(
                        'name',
                        'reference_key',
                    )->latest()->get();
                }

                if ($builderType === 'footer') {
                    $footerDesign = $builderSettings;
                    $allSections = EcommerceHeaderFooter::where([
                        'account_id' => $accountId,
                        'is_header' => false,
                    ])->select(
                        'name',
                        'reference_key',
                    )->latest()->get();
                }
                break;
            case "popup":
                $popupDesign = $builderSettings;
                $allSections = $popups;
                break;
            case "template":
                $templateDesign = $builderSettings;
                break;
            case "userTemplate":
                $userTemplateDesign = $builderSettings;
                break;
        }

        return response()->json([
            'builderType' => $builderType,
            'funnelSettings' => $funnelSettings ?? null,
            'builderSettings' => $builderSettings,
            'headerDesign' => $headerDesign ?? null,
            'landingDesign' => $landingDesign ?? null,
            'pageDesign' => $pageDesign ?? null,
            'footerDesign' => $footerDesign ?? null,
            'popupDesign' => $popupDesign ?? null,
            'templateDesign' => $templateDesign ?? null,
            'userTemplateDesign' => $userTemplateDesign ?? null,
            'allSections' => $allSections ?? [],
            'popups' => $popups ?? [],
            'isDefaultPage' => $isDefaultPage ?? false,
            'publishDomain' => $publishDomain ?? $subdomain,
            'storeDetails' => $storeDetails ?? [],
            'menuListArray' => $menuListArray ?? [],
        ]);
    }

    public function publishBaseData(Request $request)
    {
        $path = $request->path;
        $domain = AccountDomain::ignoreAccountIdScope()->where([
            'domain' => $request->hostname,
            'is_verified' => true,
        ])->firstOrFail();
        $accountId = $domain->account_id;
        $currency = Currency::where('account_id', $accountId)->where('isDefault', 1)->first();
        $isCustomerAuthenticated = Auth::guard('ecommerceUsers')->check();

        $isOnlineStorePath = count(explode("/", $path)) > 3;

        if ($path === '/') {
            $pageType = $domain?->type;
        } else if ($isOnlineStorePath) {
            $pageType = 'online-store';
        } else {
            $landing = Page::ofPublished()->where([
                'account_id' => $accountId,
                'path' => explode("/", $path)[1],
            ])->select('funnel_id', 'is_landing')->first();
            $pageType = $landing?->is_landing
                ? 'funnel'
                : 'online-store';
        }

        $funnel = funnel::ignoreAccountIdScope()->select(
            'id',
            'tracking_header',
            'tracking_body',
            'has_affiliate_badge',
        )->find($landing?->funnel_id ?? $domain?->type_id);

        $popups = Popup::ignoreAccountIdScope()->where([
            'account_id' => $accountId,
        ])->select(
            'id',
            'name',
            'design',
            'configurations',
            'reference_key',
        )->latest()->get();

        if ($domain && $pageType === "online-store") {
            if (!str_starts_with($path, "/checkout/")) {
                $headerDesign = EcommerceHeaderFooter::ofActive()->ofType(true, $accountId)->select('design')->first();
                $footerDesign = EcommerceHeaderFooter::ofActive()->ofType(false, $accountId)->select('design')->first();
                $menuListArray = EcommerceNavBar::select(
                    'id',
                    'title',
                    'menu_items'
                )->whereAccountId($accountId)->get();
            }
            $storePreferences = EcommercePreferences::select([
                'ga_header',
                'ga_bodytop',
                'has_affiliate_badge'
            ])->where('account_id', $accountId)->first();
        }

        $accountDetails = Account::select([
            'favicon',
            'company_logo',
            'store_name',
        ])->find($accountId);

        $affiliateId = AffiliateUser::firstWhere(
            'email',
            Account::find($accountId)?->user()?->first()?->email,
        )?->affiliate_unique_id ?? '';

        //* Products
        $products = UsersProduct::with(['variant_details', 'variant_values'])->where([
            'account_id' => $accountId,
            'status' => 'active',
        ])->latest()->get()->map(function ($product) {
            $product->categories = $product->categories()->without('products')->get();
            return $product;
        });
        $this->setVariantLabel($products);
        $this->setSoldQuantity($accountId, $products);

        $affiliateCookie = (new AffiliateCookieService)->get($accountId);

        return response()->json([
            'pageType' => $pageType ?? null,
            'domain' => $domain,
            'products' => $products,
            'currency' => $currency,
            'clientID' => Str::uuid(),
            'isCustomerAuthenticated' => $isCustomerAuthenticated,
            'headerDesign' => $headerDesign ?? null,
            'footerDesign' => $footerDesign ?? null,
            'menuListArray' => $menuListArray ?? [],
            'accountDetails' => $accountDetails ?? null,
            'storePreferences' => $storePreferences ?? null,
            'affiliateId' => $affiliateId ?? null,
            'funnel' => $funnel ?? null,
            'popups' => $popups ?? [],
            'affiliateCookie' => $affiliateCookie ?? null,
        ]);
    }

    /**
     * Show page associated with the domain
     * @param  string $domainName
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $path = $request->path;
        $hostname = $request->hostname;
        $domain = AccountDomain::ignoreAccountIdScope()->whereDomain($hostname)->firstOrFail();
        $accountId = $domain->account_id;
        $query = ['account_id' => $accountId];

        if ($path) {
            $query['path'] = $path;
        } else {
            switch ($domain->type) {
                case 'funnel':
                    $funnel = funnel::ignoreAccountIdScope()->findOrFail($domain->type_id);
                    $query['id'] = $funnel->landingpages()->where('is_published', true)->orderBy('index')->firstOrFail()->id;
                    break;
                case 'online-store':
                    $query['is_landing'] = false;
                    $query['id'] = $domain->type_id;
                    break;
            }
        }

        $page = Page::ofPublished()->where($query)->select(
            'id',
            'account_id',
            'funnel_id',
            'design',
            'seo_title',
            'seo_meta_description',
            'fb_title',
            'fb_description',
            'fb_image',
            'fonts',
            'index',
            'is_published',
            'tracking_header',
            'tracking_bodytop',
            'reference_key',
        )->firstOrFail();

        if ($page?->funnel_id) {
            $funnel  = funnel::ignoreAccountIdScope()->findOrFail($page->funnel_id);
            $allSections = $funnel->landingpages()->orderBy('index')->select(
                'name',
                'is_published',
                'index',
                'path',
                'reference_key'
            )->get();
        }

        return response()->json([
            'page' => $page,
            'allSections' => $allSections ?? [],
        ]);
    }

    /**
     * Turn path into lowercase, remove special characters other than - and
     * replace space with -
     * @param string $path
     * @param string
     */
    private function sanitizePath($path)
    {
        $path = preg_replace("/[^a-z0-9- ]/", "", strtolower($path));
        return str_replace(' ', '-', $path);
    }

    /**
     * Check whether the user-entered path existed and 
     * appended duplicated count if duplicated
     * @param string $path
     * @param string
     */
    public function generateUniquePath($path, $accountId)
    {
        $count = 0;
        $path = $this->sanitizePath($path);
        do {
            $newPath = $count == 0 ? $path : $path . "-" . $count;
            $isExisted = Page::where([
                'account_id' => $accountId,
                'path' => $newPath,
            ])->exists();
            $count++;
        } while ($isExisted);

        return $newPath;
    }

    /**
     * Update builder design and metadata.
     *
     * @param \Illuminate\Http\Request  $request
     * @return Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id = $request->id;
        $design = null;
        $accountId = $request->user()->currentAccountId;

        switch ($request->builderType) {
            case "header":
            case "footer":
                $design = EcommerceHeaderFooter::whereAccountId($accountId)->findOrFail($id);
                $updatedSection = $design->update([
                    'name' => $request->name,
                    'element' => $request->element,
                    'design' => $request->design,
                    'is_sticky' => $request->is_sticky,
                ]);
                break;
            case "page":
            case "landing":
                $design = Page::whereAccountId($accountId)->findOrFail($id);
                $pagePath = $request->path ?? $design->path;
                $newPagePath = ($design->path == $this->sanitizePath($pagePath))
                    ? $pagePath
                    : $this->generateUniquePath($pagePath, $accountId);
                $updatedSection = $design->update([
                    'name' => $request->name,
                    'element' => $request->element,
                    'design' => $request->design,
                    'path' => $newPagePath,
                    'seo_title' => $request->seo_title,
                    'seo_meta_description' => $request->seo_meta_description,
                    'fb_title' => $request->fb_title,
                    'fb_description' => $request->fb_description,
                    'fb_image' => $request->fb_image,
                    'tracking_header' => $request->tracking_header,
                    'tracking_bodytop' => $request->tracking_bodytop,
                    'page_width' => $request->page_width,
                    'fonts' => $request->fonts,
                ]);
                break;
            case "popup":
                $design = Popup::whereAccountId($accountId)->findOrFail($id);
                $updatedSection = $design->update([
                    'name' => $request->name ?? $design->name,
                    'element' => $request->element,
                    'design' => $request->design,
                    'is_publish' => $request->is_publish ?? false,
                    'configurations' => $request->configurations,
                ]);
                break;
            case "template":
                $design = AllTemplate::findOrFail($id);
                $updatedSection = $design->update([
                    'name' => $request->name ?? $design->name,
                    'element' => $request->element,
                    'design' => $request->design,
                    'is_published' => $request->is_publish ?? false,
                ]);
                break;
        }

        return response()->json([
            'isUpdated' => $updatedSection,
            'updateDesign' => $design ?? null,
        ]);
    }

    /**
     * Update the is_published to true/false of page whenever the user
     * publish/unpublish the page
     *
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, Page $page)
    {
        $page->update([
            'is_published' => $request?->is_published ?? true,
        ]);

        return response()->json([
            'isPublished' => $page->is_published,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
