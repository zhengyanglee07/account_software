<?php

namespace App\Http\Controllers;

use Auth;
use App\Badges;
use App\funnel;
use App\Account;
use App\Category;
use App\Currency;
use App\PaymentAPI;
use App\Page;
use App\ProcessedTag;
use App\UsersProduct;
use App\AccountDomain;
use App\StoreThemeVariable;
use App\ProductReview;
use App\EcommerceNavBar;
use App\AccountPlanTotal;
use Illuminate\Http\Request;
use App\EcommercePreferences;
use App\ProductReviewSetting;
use App\EcommerceHeaderFooter;
use App\peopleCustomFieldName;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Traits\CurrencyConversionTraits;

class LandingPageController extends Controller
{

    use CurrencyConversionTraits;

    /**
     * TODO(darren) combine this controller into BuilderController
     */

    public function accountId()
    {
        return Auth::user()->currentAccountId;
    }

    public function generateReferenceKey()
    {
        do {
            $randomId = random_int(100000000001, 999999999999);
            $condition = Page::where('reference_key', $randomId)->exists();
        } while ($condition);

        return $randomId;
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
    public function generateUniquePath($path)
    {
        $count = 0;
        $path = $this->sanitizePath($path);
        do {
            $newPath = $count == 0 ? $path : $path . "-" . $count;
            $isExisted = Page::where([
                'account_id' => $this->accountId(),
                'path' => $newPath,
            ])->exists();
            $count++;
        } while ($isExisted);

        return $newPath;
    }

    public function create(Request $request)
    {
        $isPage = $request->sectionType == "page";

        $newPage = Page::create([
            'element' => $request->newObject,
            'account_id' => $this->accountId(),
            'funnel_id' => $isPage ? null : $request->funnelId,
            'index' => $isPage ? null : $request->pageIndex,
            'name' => $request->pageName,
            'path' => $this->generateUniquePath($request->pageName),
            'reference_key' => $this->generateReferenceKey(),
            'is_landing' => $isPage ? false : true,
        ]);

        return response()->json([
            'type' => $isPage ? "page" : "landing",
            'reference_key' => $newPage->reference_key
        ]);
    }

    public function createFromFunnelTemplate(Request $request)
    {
        foreach ($request->nameArray as $key => $value) {
            Page::create([
                'account_id' => $this->accountId(),
                'funnel_id' => $request->id,
                'name' => $value,
                'path' => preg_replace("/[^a-z0-9-]/", "", strtolower($value)),
                'index' => $key,
                'reference_key' => $this->generateReferenceKey()
            ]);
        }
    }

    public function insertTemplate(Request $request)
    {
        Page::find($request->id)->update([
            'element' => $request->templateObject,
            'name' => $request->pageName,
            'path' => $this->generateUniquePath($request->pageName),
        ]);

        return response()->json(Page::find($request->id)->reference_key);
    }

    /**
     * ! Deprecated
     */
    // public function edit($builderType, $refKey)
    // {
    // 	$accountId = $this->accountId();
    //     $siteLogo = Account::find($accountId)->company_logo ?? '/images/hypershapes-logo.png';
    // 	$matchAccountId = [
    // 		'account_id' => $accountId
    // 	];
    // 	$defaultCurrency = Currency::where($matchAccountId)->firstWhere('isDefault','1')->currency;
    // 	$generalCompacts = [
    // 		'isFreePlan', 'allProducts', 'landingSettings', 'allLandingPages', 'themeVariables',
    // 		'customFields', 'publishDomain', 'builderType', 'categories', 'siteLogo', 'currencyDetails','defaultCurrency'
    // 	];

    //     // $isFreePlan = checkFeaturePermission('add-customfield') == true ? 1 : 0;
    //     $isFreePlan = false;
    //     $allProducts = UsersProduct::where($matchAccountId)->whereStatus('active')->get();
    // 	$customFields = peopleCustomFieldName::where($matchAccountId)->select(['custom_field_name', 'type'])->get();
    // 	$publishDomain = AccountDomain::where($matchAccountId)->where(function($query){
    // 		return $query->where('type', 'store')->orwhere('type', 'mini-store');
    // 	})->first()->domain;
    //     $categories = Category::where($matchAccountId)->get();

    // 	$allLandingPages = Page::where($matchAccountId)->get();

    // 	$reviewSettings = ProductReviewSetting::where($matchAccountId)->first();
    // 	$displayReview = $reviewSettings!==null ? $reviewSettings->display_review : 1;
    //     foreach($allProducts as $product) {
    // 		$reviews = ProductReview::where(['account_id'=>$accountId, 'status'=>'published', 'product_id'=>$product->id])->get();
    // 		$totalRate = 0;
    // 		$avgRate = 0;
    // 		foreach($reviews as $review){
    // 			$totalRate += $review->star_rating;
    // 			$review->productTitle = UsersProduct::where(['account_id'=>$this->accountId(), 'id'=>$review->product_id])->first()->productTitle;
    // 		}
    // 		if($totalRate!==0){
    // 			$avgRate = $totalRate/count($reviews);
    // 		}
    // 		$badge = Badges::where($matchAccountId)->orderBy('priority', 'asc')->first();
    // 		if($badge){
    // 			if($badge->select_products!=='all'){
    // 				$badge = UsersProduct::find($product->id)->badges()->orderBy('priority', 'asc')->first();
    // 			}
    // 		}
    // 		$starWidth = intval(($avgRate - intval($avgRate))* 100);
    //         $product['categories'] = $product->categories;
    // 		$product['combinations'] = $product->variant_details;
    // 		$product['avgRate'] = $avgRate;
    // 		$product['reviewCount'] = count($reviews);
    // 		$product['starWidth'] = $starWidth;
    // 		$product['displayReview'] =  1;
    // 		$product['badge'] =  $badge;
    // 		if($displayReview!==null){
    // 			$product['displayReview'] = $displayReview ;
    // 		}

    // 		$selectedSaleChannels = [];
    // 		foreach($product->saleChannels()->get() as $saleChannel){
    // 			$selectedSaleChannels[] = $saleChannel->type;
    // 		};
    // 		if($product->status==='active'&& count($product->saleChannels()->get())===0)$selectedSaleChannels = ['funnel','online-store','mini-store'];
    // 		$product['selectedSaleChannels'] =  $selectedSaleChannels;
    //     }

    //     $currencyDetails = $this->getCurrencyArray(null);
    // 	$themeVariables = StoreThemeVariable::first();

    // 	switch($builderType) {
    // 		case "header" :
    // 		case "footer" :
    // 			$menuListArray = EcommerceNavBar::select(
    // 				'id', 'title', 'menu_items'
    // 			)->where($matchAccountId)->get();
    // 			$landingSettings = EcommerceHeaderFooter::where($matchAccountId)->where('reference_key', $refKey)->firstOrFail();
    // 			$extraCompacts = ['menuListArray'];
    // 			break;
    // 		case "landing" :
    // 			$landingSettings = Page::where($matchAccountId)->where('reference_key', $refKey)->firstOrFail();
    // 			$funnelSettings = funnel::findOrFail($landingSettings->funnel_id);
    // 			$allOtherLandingPages = Page::where($matchAccountId)->where([
    // 				[ 'id', '!=', $landingSettings->id ],
    // 				[ 'funnel_id', $landingSettings->funnel_id ]
    // 			])->select('id', 'name')->get();
    // 			// TODO(tommy) refactor here
    // 			$funnelCurrency = $funnelSettings->currency;
    // 			$currencyDetails = $this->getCurrencyArray($funnelCurrency);
    // 			$stripePaymentAPI = PaymentAPI::where('account_id', $accountId)->first();

    // 			$extraCompacts = [
    // 				'funnelSettings', 'stripePaymentAPI',
    // 				'allOtherLandingPages'
    // 			];
    // 			break;
    // 		case "page":
    // 			$landingSettings = Page::where($matchAccountId)->where('reference_key', $refKey)->firstOrFail();
    // 			break;
    // 		case 'popup':
    // 			$landingSettings = (Object) [
    // 				'account_id' => $accountId,
    // 			];
    // 			$menuListArray = EcommerceNavBar::select(
    // 				'id', 'title', 'menu_items'
    // 				)->where($matchAccountId)->get();
    // 			$extraCompacts = ['menuListArray'];
    // 				break;
    // 	}
    //     return view('builder', compact([
    // 		...$generalCompacts, ...($extraCompacts ?? [])
    // 	]));
    // }

    public function update(Request $request)
    {
        $landingId = $request->input('pageSettings.id');
        $page = Page::find($landingId);

        $pageName =
            $request->input('pageSettings.name') ?? $page->name;
        $pagePath =
            $request->input('pageSettings.path') ?? $page->path;
        $newPagePath =
            $page->path == preg_replace("/[^a-z0-9-]/", "", strtolower($pagePath))
            ? $pagePath
            : $this->generateUniquePath($pagePath);

        $page->update([
            'element' => $request->element,
            'name' => $pageName,
            'path' => $newPagePath,
            'seo_title' => $request->input('pageSettings.seo_title'),
            'seo_meta_description' => $request->input('pageSettings.seo_meta_description'),
            'fb_title' => $request->input('pageSettings.fb_title'),
            'fb_description' => $request->input('pageSettings.fb_description'),
            'fb_image' => $request->input('pageSettings.fb_image'),
            'tracking_header' => $request->input('pageSettings.tracking_header'),
            'tracking_bodytop' => $request->input('pageSettings.tracking_bodytop'),
            'page_width' => $request->input('pageSettings.page_width'),
        ]);

        return response()->json($page);
    }

    public function duplicate(Request $request)
    {
        $matchFunnel = [
            'account_id' => $this->accountId(),
            'funnel_id' => $request->funnelId,
        ];

        $parentLandingPage = Page::where($matchFunnel)->findOrFail($request->landingId);
        $highestIndex = Page::where($matchFunnel)->pluck('index')->max();

        $newLandingPage = Page::create([
            'funnel_id' => $parentLandingPage->funnel_id,
            'account_id' => $this->accountId(),
            'is_published' => $parentLandingPage->is_published,
            'element' => $parentLandingPage->element,
            'name' => 'Copy of ' . $parentLandingPage->name,
            'index' => $highestIndex + 1,
            'path' => $this->generateUniquePath($parentLandingPage->path),
            'reference_key' => $this->generateReferenceKey()
        ]);

        return response()->json($newLandingPage);
    }

    public function delete(Page $landing)
    {
        $landing->delete();

        if (!$landing->is_landing) {
            return response()->json([
                'status' => 'Success',
                'message' => "Page {$landing->name} is successfully deleted"
            ]);
        }

        $accountPlanCalculation = AccountPlanTotal::where('account_id', Auth::user()->currentAccountId)->first();
        $total_landing = $accountPlanCalculation->total_landingpage;

        $funnelLandingPages = Page::where('funnel_id', $landing->funnel_id)->get();

        foreach ($funnelLandingPages as $landingpage) {
            if ($landingpage->index > $landing->index) {
                $landingpage->update(['index' => $landingpage->index - 1]);
            }
        }

        return response()->json([
            'accountLandingTotal' => $total_landing,
            'funnelLandingPages' => $funnelLandingPages
        ]);
    }


    public function updateStatus(Page $landing, Request $request)
    {
        $landing->update([
            'is_published' => $request->is_published,
        ]);

        if ($request->funnelId != null) {
            return response()->json(
                Page::where('funnel_id', $request->funnelId)->get()
            );
        }

        return response()->json([
            'status' => 'Success',
            'message' => 'Page status was updated successfully',
            'updatedRecord' => $landing
        ]);
    }


    /**
     * ! Deprecated
     */
    // public function preview($referenceKey, Request $request)
    // {
    //     $status = "Preview";
    //     $accountId = $this->accountId();
    //     $pageType = $request->query('type');
    //     $matchAccountId = ['account_id' => $accountId];
    //     $allProducts = UsersProduct::where($matchAccountId)->get();
    //     $allLandingPages = Page::where($matchAccountId)->get();

    //     $defaultCurrency = Currency::where($matchAccountId)->firstWhere('isDefault', '1')->currency;
    //     $hasAffiliateBadge = EcommercePreferences::firstWhere($matchAccountId)->value('has_affiliate_badge') ?? true;
    //     $favicon = Account::find($accountId)->favicon;
    //     $siteLogo = Account::find($accountId)->company_logo ?? '/images/hypershapes-logo.png';

    //     $reviewSettings = ProductReviewSetting::where($matchAccountId)->first();
    //     $displayReview = $reviewSettings !== null ? $reviewSettings->display_review : 1;
    //     foreach ($allProducts as $product) {
    //         $reviews = ProductReview::where(['account_id' => $accountId, 'status' => 'published', 'product_id' => $product->id])->get();
    //         $totalRate = 0;
    //         $avgRate = 0;
    //         foreach ($reviews as $review) {
    //             $totalRate += $review->star_rating;
    //             $review->productTitle = UsersProduct::where(['account_id' => $this->accountId(), 'id' => $review->product_id])->first()->productTitle;
    //         }
    //         if ($totalRate !== 0) {
    //             $avgRate = $totalRate / count($reviews);
    //         }
    //         $badge = Badges::where($matchAccountId)->orderBy('priority', 'asc')->first();
    //         if ($badge) {
    //             if ($badge->select_products !== 'all') {
    //                 $badge = UsersProduct::find($product->id)->badges()->orderBy('priority', 'asc')->first();
    //             }
    //         }
    //         $starWidth = intval(($avgRate - intval($avgRate)) * 100);
    //         $product['categories'] = $product->categories;
    //         $product['combinations'] = $product->variant_details;
    //         $product['avgRate'] = $avgRate;
    //         $product['reviewCount'] = count($reviews);
    //         $product['badge'] = $badge;
    //         $product['starWidth'] = $starWidth;
    //         $product['displayReview'] = $displayReview;

    //         $selectedSaleChannels = [];
    //         foreach ($product->saleChannels()->get() as $saleChannel) {
    //             $selectedSaleChannels[] = $saleChannel->type;
    //         };
    //         if ($product->status === 'active' && count($product->saleChannels()->get()) === 0) $selectedSaleChannels = ['funnel', 'online-store', 'mini-store'];
    //         $product['selectedSaleChannels'] =  $selectedSaleChannels;
    //     }

    //     $bladeFile =
    //         $pageType == "landing"
    //         ? 'previewpage'
    //         : 'ecommercePreviewPage';

    //     if ($pageType == 'page' || $pageType == 'landing') {
    //         $landingSettings = Page::where($matchAccountId)->where('reference_key', $referenceKey)->firstOrFail();
    //         $landingSettings->popups = $landingSettings->popups()->allRelatedIds();
    //     } else {
    //         $landingSettings = EcommerceHeaderFooter::where($matchAccountId)->where('reference_key', $referenceKey)->firstOrFail();
    //     }

    //     $pageName = $landingSettings->id;

    //     $headerSettings = EcommerceHeaderFooter::firstWhere([
    //         'account_id' => $accountId,
    //         'is_active' => true,
    //         'sectionType' => 'header'
    //     ]) ?? '{}';


    //     $footerSettings = EcommerceHeaderFooter::firstWhere([
    //         'account_id' => $accountId,
    //         'is_active' => true,
    //         'sectionType' => 'footer'
    //     ]) ?? '{}';

    //     $menuListArray = EcommerceNavBar::select(
    //         'id',
    //         'menu_items'
    //     )->where($matchAccountId)->get();

    //     $categories = Category::where($matchAccountId)->get();

    //     // TODO refactor here
    //     if ($pageType == "landing") {
    //         $funnel = funnel::find($landingSettings->funnel_id);
    //         $hasAffiliateBadge = $funnel->has_affiliate_badge ?? true;
    //         $firstLandingId = Page::where($matchAccountId)->where('funnel_id', $funnel->id)->where('index', '0')->first()->id;
    //         $latestLandingId = Page::where($matchAccountId)->where('funnel_id', $funnel->id)->orderBy('index', 'DESC')->first()->id;
    //         $funnelCurrency = $funnel->currency;
    //         $currencyDetails = $this->getCurrencyArray($funnelCurrency);
    //         $stripePaymentAPI = PaymentAPI::where($matchAccountId)->where('payment_methods', 'Stripe')->first();

    //         $extraCompacts = [
    //             'funnel', 'firstLandingId', 'latestLandingId',
    //             'stripePaymentAPI', 'funnelCurrency', 'currencyDetails',
    //         ];
    //     }
    //     $isCustomerLoggedIn = Auth::guard('ecommerceUsers')->check();

    //     return view($bladeFile, compact([
    //         ...$extraCompacts ?? [],
    //         'landingSettings', 'headerSettings', 'footerSettings', 'hasAffiliateBadge', 'siteLogo', 'allLandingPages',
    //         'status', 'pageType', 'allProducts', 'menuListArray', 'defaultCurrency', 'favicon', 'pageName', 'isCustomerLoggedIn',
    //         'categories',
    //     ]));
    // }

    public function nextStepInFunnel($landingId, Request $request)
    {
        $landing = Page::findOrFail($landingId);
        $query = Page::where([
            'account_id' => $landing->account_id,
            'funnel_id' => $landing->funnel_id
        ]);

        if ($request->query('status') == "Preview") {
            $nextLanding = $query->firstWhere(
                'index',
                $landing->index + 1
            );
        } else {
            $nextLanding = $query->where([
                ['index', '>', $landing->index],
                ['is_published', true],
            ])->orderBy('index', 'asc')->first();
        }

        if ($nextLanding == null) {
            return response()->json([
                'isNull' => true
            ]);
        }

        $funnelDomain = funnel::findOrFail($landing->funnel_id)->domain_name;

        return response()->json([
            'domain' => $funnelDomain,
            'path' => $nextLanding->path,
            'refKey' => $nextLanding->reference_key
        ]);
    }

    public function otherStepInFunnel($landingId, Request $request)
    {
        $funnelId = $request->query('funnelId');

        if ($funnelId != null) {
            $nextLanding = Page::firstWhere([
                'funnel_id' => $funnelId,
                'index' => 0
            ]);
        } else {
            $nextLanding = Page::whereId($landingId)->when(
                $request->query('type') == "Publish",
                function ($query) {
                    return $query->where('is_published', true);
                }
            )->first();
        }

        $jsonData =
            $nextLanding == null
            ? [
                'isNull' => true
            ]
            : [
                'path' => $nextLanding->path,
                'refKey' => $nextLanding->reference_key,
                'domain' => funnel::findOrFail($nextLanding->funnel_id)->domain_name,
            ];

        return response()->json($jsonData);
    }
}
