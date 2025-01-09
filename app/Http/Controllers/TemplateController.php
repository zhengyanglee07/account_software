<?php

namespace App\Http\Controllers;

use Auth;
use App\Badges;
use App\funnel;
use App\Account;

use App\Category;
use App\Currency;
use App\PaymentAPI;
use App\AllTemplate;
use App\EmailDesign;
use App\Page;
use App\UsersProduct;
use App\UserTemplate;
use App\AccountDomain;
use App\ProductReview;
use App\EcommerceNavBar;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\EcommercePreferences;
use App\ProductReviewSetting;
use App\EcommerceHeaderFooter;
use Illuminate\Support\Facades\Storage;
use App\Traits\CurrencyConversionTraits;
use Inertia\Inertia;

class TemplateController extends Controller
{
    use CurrencyConversionTraits;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $templateAuthors = array(
                'steve@rocketlaunch.my',
                'tzyong990808@gmail.com',
            );
            if (app()->environment() === "production" && !in_array(Auth::user()->email, $templateAuthors)) {
                abort(403, 'Access denied');
            }
            return $next($request);
        })->only(['show', 'create', 'edit', 'updateDetails', 'destroy']);
    }

    public function index(Request $request)
    {
        $allUserTemplates = UserTemplate::whereAccountId($request->user()->currentAccountId)->latest()->get();
        $allGeneralTemplates = AllTemplate::where('is_published', true)->get();

        return response()->json([
            'userTemplates' => $allUserTemplates,
            'generalTemplates' => $allGeneralTemplates,
        ]);
    }

    public function show()
    {
        // $emailTemplates = EmailDesign::whereNull('account_id')->whereNotNull('reference_key')->get()->map(function ($item) {
        // 	$item['title'] = $item->name;
        // 	$item['type'] = "email";
        // 	$emailRefKey = $item->email ? $item->email->reference_key : null;
        // 	$item['preview_url'] = '/emails/design/template/' . $item->reference_key . '/preview';
        // 	$item['builder_url'] = '/emails/' . $emailRefKey . '/design/' . $item->reference_key . '/edit?source=template&key=' . $emailRefKey;
        // 	$item['status'] = $item->template_status_id == 2 ? "Publish" : "Draft";
        // 	$item['tags'] = [""];
        // 	return $item;
        // });

        $allTemplates = AllTemplate::select("id", "name", "type", "is_published", "tags", "image_path")->latest()->get()->map(function ($item) {
            $item['preview_url'] = '/builder/template/' . $item->id . '/preview';
            $item['builder_url'] = '/builder/template/' . $item->id . '/editor';
            return $item;
        });

        // $allTemplates = $emailTemplates->merge($otherTemplates)->sortByDesc('created_at');

        return Inertia::render('template/pages/AllTemplate', compact('allTemplates'));
    }

    public function create(Request $request)
    {
        $template = AllTemplate::create([
            'name' => $request->name,
            'type' => $request->type,
            'tags' => $request->tags,
            'element' => $request->element,
        ]);
        return response()->json([
            'id' => $template->id,
            'isEditDetails' => false,
            'message' => "Template created successfully"
        ]);
    }

    public function saveSnapshot(Request $request)
    {
        $templateId = $request->id;
        $imageName = "snapshot_template_" . $templateId . '.jpeg';
        $imagePath = "generalTemplates/" . $imageName;
        $tempImageLocation = '/app/public/media/' . $imageName;
        $data = base64_decode(explode(";base64,", $request->image)[1]);

        //* get dimension of original image
        $originalImage = imagecreatefromstring($data);
        $originalWidth = imagesx($originalImage);
        $originalHeight = imagesy($originalImage);

        //* set the dimension of resized image
        $resizedWidth = $originalWidth / 4;
        $resizedHeight = $originalHeight / 4;

        //* create a empty image with resized dimension
        $resizedImage = imagecreatetruecolor($resizedWidth, $resizedHeight);

        //* copy and resize part of an image with resampling
        imagecopyresampled($resizedImage, $originalImage, 0, 0, 0, 0, $resizedWidth, $resizedHeight, $originalWidth, $originalHeight);

        //* compress, convert image to .jpeg, and save it in temporary location
        imagejpeg($resizedImage, storage_path() . $tempImageLocation, 70);
        imagedestroy($resizedImage);

        Storage::disk('s3')->put(
            $imagePath,
            file_get_contents(storage_path($tempImageLocation)),
            'public'
        );

        $isLocal = app()->environment() === 'local';
        $imageHost = $isLocal
            ? config('filesystems.disks.s3.url')
            : config('filesystems.disks.s3.bucket');
        $imageUrl = $isLocal
            ? $imageHost . '/' . config('filesystems.disks.s3.bucket') . "/{$imagePath}"
            : "https://" . $imageHost . "/{$imagePath}";

        $template = AllTemplate::where('id', $templateId)->firstOrFail();
        $template->update([
            'image_path' => $imageUrl,
        ]);

        return response()->json([
            'updatedTemplate' => $template,
        ]);
    }

    /**
     * @deprecated
     */
    // public function edit($id, $type)
    // {
    //     $status = "On Edit";
    //     $builderType = "template";
    //     $funnelCurrency = "MYR";
    //     $accountId = Auth::user()->currentAccountId;

    //     $matchAccountId = [
    //         'account_id' => $accountId
    //     ];
    //     $menuListArray = EcommerceNavBar::select(
    //         'id',
    //         'title',
    //         'menu_items'
    //     )->where($matchAccountId)->get();

    //     $categories = Category::where($matchAccountId)->get();
    //     $siteLogo = Account::find($accountId)->company_logo ?? '/images/hypershapes-logo.png';
    //     $stripePaymentAPI = PaymentAPI::firstWhere($matchAccountId);
    //     $publishDomain = AccountDomain::where($matchAccountId)->firstWhere('is_subdomain', true)->domain;
    //     //* landingSettings == template, temporarily use this name first for rapid dev
    //     $landingSettings = collect(AllTemplate::findOrFail($id))->put('account_id', $accountId);
    //     $defaultCurrency = Currency::where($matchAccountId)->firstWhere('isDefault', '1')->currency;
    //     $allProducts = UsersProduct::where($matchAccountId)->get()->map(function ($product) {
    //         $product['categories'] = $product->categories;
    //         $product['combinations'] = $product->variant_details;
    //         return $product;
    //     });
    //     $allLandingPages = Page::where($matchAccountId)->get();

    //     $reviewSettings = ProductReviewSetting::where($matchAccountId)->first();
    //     $displayReview = $reviewSettings !== null ? $reviewSettings->display_review : 1;
    //     foreach ($allProducts as $product) {
    //         $reviews = ProductReview::where(['account_id' => $accountId, 'status' => 'published', 'product_id' => $product->id])->get();
    //         $totalRate = 0;
    //         $avgRate = 0;
    //         foreach ($reviews as $review) {
    //             $totalRate += $review->star_rating;
    //             $review->productTitle = UsersProduct::where(['account_id' => $accountId, 'id' => $review->product_id])->first()->productTitle;
    //         }
    //         if ($totalRate !== 0) {
    //             $avgRate = $totalRate / count($reviews);
    //         }
    //         $badge = Badges::where('account_id', $accountId)->orderBy('priority', 'asc')->first();
    //         if ($badge) {
    //             if ($badge->select_products !== 'all') {
    //                 $badge = UsersProduct::find($product->id)->badges()->orderBy('priority', 'asc')->first();
    //             }
    //         }
    //         $starWidth = intval(($avgRate - intval($avgRate)) * 100);
    //         $product['avgRate'] = $avgRate;
    //         $product['reviewCount'] = count($reviews);
    //         $product['starWidth'] = $starWidth;
    //         $product['displayReview'] = $displayReview;
    //         $product['badge'] = $badge;

    //         $selectedSaleChannels = [];
    //         foreach ($product->saleChannels()->get() as $saleChannel) {
    //             $selectedSaleChannels[] = $saleChannel->type;
    //         };
    //         if ($product->status === 'active' && count($product->saleChannels()->get()) === 0) $selectedSaleChannels = ['funnel', 'online-store', 'mini-store'];
    //         $product['selectedSaleChannels'] =  $selectedSaleChannels;
    //     }
    //     $currencyDetails = $this->getCurrencyArray($funnelCurrency);

    //     return Inertia::render('builder/pages/BaseBuilder', compact(
    //         'status',
    //         'allProducts',
    //         'landingSettings',
    //         'menuListArray',
    //         'builderType',
    //         'currencyDetails',
    //         'stripePaymentAPI',
    //         'funnelCurrency',
    //         'publishDomain',
    //         'categories',
    //         'siteLogo',
    //         'allLandingPages'
    //     ));
    // }

    /**
     * @deprecated
     */
    // public function preview(Request $request, $id)
    // {
    //     $status = "Preview";
    //     $pageType = "template";
    //     $funnelCurrency = "MYR";
    //     $hasAffiliateBadge = false;
    //     $accountId = Auth::user()->currentAccountId;
    //     $type = $request->query('type') ?? "builder";
    //     $allProducts = UsersProduct::whereAccountId($accountId)->get();
    //     $allLandingPages = Page::whereAccountId($accountId)->get();
    //     $stripePaymentAPI = PaymentAPI::whereAccountId($accountId)->first();
    //     $siteLogo = Account::find($accountId)->company_logo ?? '/images/hypershapes-logo.png';
    //     $menuListArray = EcommerceNavBar::where([
    //         'account_id' => $accountId,
    //     ])->select('id', 'title', 'menu_items')->get();
    //     //* landingSettings == template, temporarily use this name first for rapid dev
    //     $landingSettings =
    //         $type == "builder"
    //         ? AllTemplate::findOrFail($id)
    //         : UserTemplate::findOrFail($id);
    //     $currencyDetails = $this->getCurrencyArray($funnelCurrency);
    //     $reviewSettings = ProductReviewSetting::where('account_id', $accountId)->first();
    //     $displayReview = $reviewSettings !== null ? $reviewSettings->display_review : 1;
    //     foreach ($allProducts as $product) {
    //         $reviews = ProductReview::where(['account_id' => $accountId, 'status' => 'published', 'product_id' => $product->id])->get();
    //         $totalRate = 0;
    //         $avgRate = 0;
    //         foreach ($reviews as $review) {
    //             $totalRate += $review->star_rating;
    //             $review->productTitle = UsersProduct::where(['account_id' => $accountId, 'id' => $review->product_id])->first()->productTitle;
    //         }
    //         if ($totalRate !== 0) {
    //             $avgRate = $totalRate / count($reviews);
    //         }
    //         $badge = Badges::where('account_id', $accountId)->orderBy('priority', 'asc')->first();
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
    //         $product['starWidth'] = $starWidth;
    //         $product['displayReview'] = $displayReview;
    //         $product['badge'] = $badge;

    //         $selectedSaleChannels = [];
    //         foreach ($product->saleChannels()->get() as $saleChannel) {
    //             $selectedSaleChannels[] = $saleChannel->type;
    //         };
    //         if ($product->status === 'active' && count($product->saleChannels()->get()) === 0) $selectedSaleChannels = ['funnel', 'online-store', 'mini-store'];
    //         $product['selectedSaleChannels'] =  $selectedSaleChannels;

    //         $isCustomerLoggedIn = Auth::guard('ecommerceUsers')->check();
    //     }
    //     return view('previewpage', compact(
    //         'status',
    //         'allProducts',
    //         'landingSettings',
    //         'pageType',
    //         'allLandingPages',
    //         'stripePaymentAPI',
    //         'menuListArray',
    //         'hasAffiliateBadge',
    //         'siteLogo',
    //         'funnelCurrency',
    //         'currencyDetails',
    //         'isCustomerLoggedIn'
    //     ));
    // }

    public function update(Request $request, AllTemplate $template)
    {
        $template->update([
            'title' => $request->title,
            'type' => $request->type,
            'tags' => $request->tags,
            'status' => $request->status,
            'template_objects' => $request->elementsObject,
            'total_id_count' => 0,
            'image_path' => $template['image_path'] ?? null,
        ]);

        return response()->noContent();
    }

    public function bulkUpdate(Request $request)
    {
        AllTemplate::whereIn('id', $request->templateIds)->update([
            'status' => $request->status ?? 'Draft',
        ]);

        return response()->noContent();
    }

    public function updateDetails(Request $request, AllTemplate $template)
    {
        $template->update([
            'name' => $request->name,
            'type' => $request->type,
            'tags' => $request->tags,
            'is_published' => $request->is_published,
        ]);
        return response()->json([
            'isEditDetails' => true,
            'updatedTemplate' => $template,
            'message' => 'Template Details updated successfully'
        ]);
    }

    public function destroy($templateId)
    {
        AllTemplate::destroy($templateId);
        return response()->json(['message' => 'Template deleted successfully']);
    }

    /**
     * User Template
     */
    public function saveUserTemplate(Request $request)
    {
        $accountId = $request->user()->currentAccountId;

        $userTemplate = UserTemplate::create([
            'account_id'  => $accountId,
            'name' => $request->name,
            'type' => ucfirst($request->type),
            'element' => json_encode($request->element),
            'design' => $request->design,
        ]);

        return response()->json([
            'template' => $userTemplate,
        ]);
    }

    public function deleteUserTemplate(UserTemplate $template)
    {
        $template->delete();

        return response()->noContent();
    }

    public function themeTemplate(AllTemplate $themeTemplate)
    {
        $accountId = Auth::user()->currentAccountId;
        $childTemplates = json_decode($themeTemplate->template_objects);
        $allProducts = UsersProduct::whereAccountId($accountId)->whereStatus('active')->get();
        $allLandingPages = Page::whereAccountId($accountId)->get();
        $storePreferences = EcommercePreferences::firstWhere('account_id', $accountId);
        $defaultCurrency = Currency::whereAccountId($accountId)->firstWhere('isDefault', '1')->currency;
        $hasAffiliateBadge = EcommercePreferences::firstWhere('account_id', $accountId)->has_affiliate_badge ?? true;

        $reviewSettings = ProductReviewSetting::where('account_id', $accountId)->first();
        $displayReview = $reviewSettings !== null ? $reviewSettings->display_review : 1;
        foreach ($allProducts as $product) {
            $reviews = ProductReview::where(['account_id' => $accountId, 'status' => 'published', 'product_id' => $product->id])->get();
            $totalRate = 0;
            $avgRate = 0;
            foreach ($reviews as $review) {
                $totalRate += $review->star_rating;
                $review->productTitle = UsersProduct::where(['account_id' => $accountId, 'id' => $review->product_id])->first()->productTitle;
            }
            if ($totalRate !== 0) {
                $avgRate = $totalRate / count($reviews);
            }
            $badge = Badges::where('account_id', $accountId)->orderBy('priority', 'asc')->first();
            if ($badge) {
                if ($badge->select_products !== 'all') {
                    $badge = UsersProduct::find($product->id)->badges()->orderBy('priority', 'asc')->first();
                }
            }
            $starWidth = intval(($avgRate - intval($avgRate)) * 100);
            $product['categories'] = $product->categories;
            $product['combinations'] = $product->variant_details;
            $product['avgRate'] = $avgRate;
            $product['reviewCount'] = count($reviews);
            $product['starWidth'] = $starWidth;
            $product['displayReview'] = $displayReview;
            $product['badge'] = $badge;

            $selectedSaleChannels = [];
            foreach ($product->saleChannels()->get() as $saleChannel) {
                $selectedSaleChannels[] = $saleChannel->type;
            };
            if ($product->status === 'active' && count($product->saleChannels()->get()) === 0) $selectedSaleChannels = ['funnel', 'online-store', 'mini-store'];
            $product['selectedSaleChannels'] =  $selectedSaleChannels;
        }

        $headerSettings = AllTemplate::find($childTemplates->header);
        $landingSettings = AllTemplate::find($childTemplates->page);
        $landingSettings['id'] = $themeTemplate->id;
        $footerSettings = AllTemplate::find($childTemplates->footer);

        // dd($headerSettings, $landingSettings, $footerSettings);

        $menuListArray = EcommerceNavBar::select(
            'id',
            'menu_items'
        )->whereAccountId($accountId)->get();

        return view('ecommercePreviewPage', [
            'status' => "Publish",
            'pageType' => "template",
            'exchangeRate' => "1",
            'allProducts' => $allProducts,
            'allLandingPages' => $allLandingPages,
            'storePreferences' => $storePreferences,
            'defaultCurrency' => $defaultCurrency,
            'headerSettings' => $headerSettings,
            'landingSettings' => $landingSettings,
            'footerSettings' => $footerSettings,
            'menuListArray' => $menuListArray,
            'hasAffiliateBadge' => $hasAffiliateBadge,
            'templateName' => $themeTemplate->title
        ]);
    }

    public function getThemes()
    {
        $generalTemplates = AllTemplate::where(['status' => 'Publish', 'type' => 'theme'])->get();
        return Inertia::render('onboarding/pages/ThemeSelection', compact('generalTemplates'));
    }
}
