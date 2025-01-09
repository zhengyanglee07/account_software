<?php

namespace App\Traits;

use Auth;
use App\Account;
use App\AffiliateUser;
use App\Currency;
use App\MiniStore;
use App\MiniStoreTheme;
use App\EcommerceNavBar;
use App\Page;
use App\EcommercePreferences;
use App\EcommerceHeaderFooter;
use App\Traits\CurrencyConversionTraits;
use App\Traits\UsersProductTrait;
use App\FacebookPixel;
use App\Models\StoreTheme;
use App\Traits\SocialProofTrait;
use App\Traits\ReferralCampaignTrait;
use App\Services\CheckoutService;

trait PublishedPageTrait
{
    use AuthAccountTrait, CurrencyConversionTraits, UsersProductTrait, SocialProofTrait, ReferralCampaignTrait;

    /**
     * A helper function to retrieve common data required by every page in publish mode
     *
     * @return array
     */
    public function getPublishedPageBaseData($type = 'page')
    {
        $domainInfo = $this->getCurrentAccountId(true);
        $domain = $domainInfo['domain'];
        $accountId = $domainInfo['accountId'];

        if ($domain && $domain->type === 'mini-store') {
            $storeDetails = MiniStore::whereAccountId($accountId)->first();
            $miniStoreTheme = MiniStoreTheme::firstOrFail();
            $headerDesign = (object)[
                'element' => $miniStoreTheme->header
            ];
            $locationModal = (new CheckoutService())->getDeliveryAreaCheckerData();
        } else {
            $storeDetails = Account::select([
                'id', 'favicon', 'company_logo', 'store_name'
            ])->find($accountId);
            $headerDesign = EcommerceHeaderFooter::ofActive()->ofType(true, $accountId)->first();
            $footerDesign = EcommerceHeaderFooter::ofActive()->ofType(false, $accountId)->first();
        }

        $affiliateId = AffiliateUser::firstWhere(
            'email',
            Account::find($accountId)?->user()?->first()?->email
        )->affiliate_unique_id ?? '';

        $menuListArray = EcommerceNavBar::select(
            'id',
            'title',
            'menu_items'
        )->whereAccountId($accountId)->get();

        $storePreferences = EcommercePreferences::select([
            'ga_header', 'ga_bodytop', 'has_affiliate_badge'
        ])->firstWhere('account_id', $accountId);

        $themeStyles = StoreTheme::first();
        $products = $this->getAllActiveProducts($accountId);
        $categories = $this->getAllCategoriesWithActiveProducts($accountId);
        $allCurrencies = Currency::currencyDetails();
        $currencyDetails = $this->getCurrency($accountId);
        $socialProof = $this->fetchAllNotification($accountId, $domain->type ?? null);
        $referralCampaigns = $this->getReferralCampaigns($accountId);

        $isLandingPage = Page::ofPublished()->ofPath($accountId, request()->path())
            ->where('is_landing', true)
            ->exists();
        if ($isLandingPage) {
            $twoStepFormData = (new CheckoutService)->getTwoStepFormData();
        }

        if (($domain['type'] ?? null) !== 'funnel' && $type !== 'landing') {
            $allPages = Page::whereAccountId($accountId)->select([
                'id',
                'is_landing',
                'path',
                'is_published',
            ])->where('is_landing', false)->get();
        }

        if (Auth::guard('ecommerceUsers')->check()) {
            $customerInfo = Auth::guard('ecommerceUsers')->user()->hasVerifiedEmail()
                ? Auth::guard('ecommerceUsers')->user()->processedContact ?? null
                : null;
        }

        return [
            'isPublish' => true,
            'domain' => $domain,
            'pageType' => $domain->type ?? null,
            'headerDesign' => $headerDesign ?? null,
            'footerDesign' => $footerDesign ?? null,
            'storeDetails' => $storeDetails ?? null,
            'affiliateId' => $affiliateId ?? '',
            'menuListArray' => $menuListArray ?? [],
            'storePreferences' => $storePreferences ?? null,
            'products' => $products ?? null,
            'categories' => $categories ?? null,
            'allCurrencies' => $allCurrencies ?? [],
            'currencyDetails' => $currencyDetails ?? null,
            'facebookPixel' => $facebookPixel ?? null,
            'socialProof' => $socialProof ?? null,
            'customerInfo' => $customerInfo ?? null,
            'referralCampaigns' => $referralCampaigns ?? null,
            'allPages' => $allPages ?? [],
            'locationModal' => $locationModal ?? null,
            'twoStepFormData' => $twoStepFormData ?? null,
            'themeStyles' => $themeStyles ?? null,
        ];
    }
}
