<?php

namespace App\Http\Controllers;

use Auth;

use App\User;
use App\Order;
use App\Badges;
use App\Account;
use App\Variant;
use App\Category;
use App\Currency;
use App\MiniStore;
use Carbon\Carbon;
use App\OrderDetail;
use App\Notification;
use App\SaleChannel;
use App\UsersProduct;
use App\VariantValue;
use App\AccountDomain;
use App\ProductReview;
use App\VariantDetails;
use App\ProductCategory;
use App\productOptionValue;
use Illuminate\Http\Request;
use App\ProductReviewSetting;
use App\EcommerceHeaderFooter;
use App\ProductRecommendation;
use App\Repository\CheckoutRepository;
use App\Traits\AuthAccountTrait;
use App\Traits\UsersProductTrait;
use App\Traits\PublishedPageTrait;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class onlineStoreController extends Controller
{
    use PublishedPageTrait, AuthAccountTrait, UsersProductTrait;

    //************************global function ********************************

    public function domainInfo()
    {
        return AccountDomain::whereDomain($_SERVER['HTTP_HOST'])->first();
    }

    public function domainType($url)
    {
        $accountId = $this->domainInfo() != null
            ? $this->domainInfo()->account_id
            : Auth::user()->currentAccountId;

        // if($url!==config('app.domain')){
        //     return AccountDomain::where('account_id', $accountId)->where('domain', $url)->first()->type;
        // }
        if (!str_contains($url, config('app.domain'))) {
            return AccountDomain::where('account_id', $accountId)->where('domain', $url)->first()->type;
        } else {
            return AccountDomain::where('account_id', $accountId)->where(function ($query) {
                $query->where('type', 'store')->orWhere('type', 'mini-store');
            })->first()->type;
        }
    }

    //************************product description page ********************************

    public function getProductVariant($product)
    {
        $variantOptionArray = array();
        $variationCombination = (object)[];
        if ($product) {
            foreach ($product->product_variants as $tempVariant) {
                $variant = Variant::find($tempVariant->variant_id);
                $obj = (object)[
                    'name' => $variant->variant_name,
                    'type' => $variant->type,
                    'is_shared' => $variant->is_shared,
                    'display_name' => $variant->display_name,
                    'valueArray' => $variant->values
                ];
                array_push($variantOptionArray, $obj);
            }
            foreach ($product->variant_details as $variant_details) {
                $key = $this->variantCombinationName($variant_details);
                $variationCombination->$key = (object)[
                    'refKey' => $variant_details->reference_key,
                    'sku' => $variant_details->sku,
                    'price' => $variant_details->price,
                    'compared_at_price' => $variant_details->comparePrice,
                    'weight' => $variant_details->weight,
                    'image_url' => $variant_details->image_url,
                    'is_visible' => $variant_details->is_visible,
                    'color' => $variant_details->color,
                    'quantity' => $variant_details->quantity,
                    'is_selling' => $variant_details->is_selling,
                ];
            }
        }
        return ['variationCombination' => $variationCombination, 'variantOptionArray' => $variantOptionArray];
    }

    public function variantCombinationName($variant)
    {
        $option_1_name = $this->findVariantName(1, $variant->option_1_id);
        $option_2_name = $this->findVariantName(2, $variant->option_2_id);
        $option_3_name = $this->findVariantName(3, $variant->option_3_id);
        $option_4_name = $this->findVariantName(4, $variant->option_4_id);
        $option_5_name = $this->findVariantName(5, $variant->option_5_id);
        return trim($option_1_name . $option_2_name . $option_3_name . $option_4_name . $option_5_name);
    }

    public function findVariantName($index, $id)
    {
        $variant = VariantValue::find($id);
        if ($variant) {
            return $index == 1 ? $variant->variant_value : " / " . $variant->variant_value;
        }
        return "";
    }

    public function showProductList()
    {
        $publishPageBaseData = $this->getPublishedPageBaseData();

        return Inertia::render('online-store/pages/AllProducts', $publishPageBaseData);
    }

    // new product description
    public function getProductInfoForDescription(Request $request)
    {
        $path = $request->path;
        $publishPageBaseData = $this->getPublishedPageBaseData();
        $domain = $publishPageBaseData['domain'];
        $accountId = $publishPageBaseData['currencyDetails']->account_id;
        $account = Account::find($accountId);
        $saleChannel =  $domain ? $domain->type : 'preview';
        $appSaleChannel = new SaleChannel();
        $activeSaleChannels = $appSaleChannel->getActiveSaleChannels($accountId) ?? [];
        $product = UsersProduct::where('account_id', $accountId)->where('path', $path)->firstOrFail();
        $product->saleChannels = $this->selectedSaleChannels($product);
        if (!in_array($saleChannel,  $product->saleChannels) || !in_array($saleChannel,  $activeSaleChannels) && $saleChannel !== 'preview') abort(404);
        $product->categories = $product->categories()->get()->map(function ($category) {
            return $category->id;
        });
        $product->productImageCollection = $this->imagesCollection(null, $product);
        $product->variants = $product->product_variants->map(function ($productVariant) {
            $variant = Variant::find($productVariant->variant_id);
            $variant->valueArray = VariantValue::where('variant_id', $variant->id)->get();
            return $variant;
        });

        if ($product->hasVariant) $product->variantPrice = $product->variant_details->min('price');
        $product->variant_details->map(function ($variantDetail) use ($product) {
            $product->productImageCollection = $this->imagesCollection($variantDetail->image_url, $product);
            $variantDetail->outOfStock = $variantDetail->quantity < 1 && !$variantDetail->is_selling || !$variantDetail->is_visible;
            $variantDetail->variations = $this->variations($variantDetail);
            return  $variantDetail;
        });

        $product->outOfStock = $product->hasVariant ? false : $product->quantity < 1 && !$product->is_selling;
        $product->customOptions =  $this->getProductOption($product);
        $reviewSettings = ProductReviewSetting::ignoreAccountIdScope()->where('account_id', $accountId)->first();
        $product->reviews = $this->reviews($accountId, $product, $reviewSettings);
        // $product->badgeLabel = $this->badgeLabel($product);
        $companyName = $this->companyName($accountId, $saleChannel);

        $productRecommendationsTypes = $account->productRecommendationsTypes()->sortBy('priority')->map(function ($productRecommendation) {
            return $productRecommendation->type;
        });

        //order detail
        if ($productRecommendationsTypes->contains('frequently-bought-together')) {
            $orders =
                DB::table('orders')->join('order_details', 'orders.id', '=', 'order_id')->whereUsersProductId($product->id)->select('orders.*',)->get()->map(function ($order) {
                    return $order->id;
                });
            $frequentlyBoughtProducts = DB::table('order_details')
                ->whereIn('order_id', $orders)->select('users_product_id', DB::raw("SUM(quantity) as total_quantity"))->groupBy('users_product_id')
                ->orderByDesc('total_quantity')
                ->get()->where('users_product_id', '!==', $product->id)->map(function ($orderDetail) {
                    return $orderDetail->users_product_id;
                });
        }

        // return response()->json(['product' => $product, 'companyName' => $companyName, 'productRecommendationsTypes' => $productRecommendationsTypes, 'frequentlyBoughtProducts' => $frequentlyBoughtProducts ?? []]);
        return Inertia::render('online-store/pages/ProductDescription', array_merge(
            $publishPageBaseData,
            [
                'selectedProduct' => $product,
                'companyName' => $companyName,
                'productRecommendationsTypes' => $productRecommendationsTypes,
                'frequentlyBoughtProducts' => $frequentlyBoughtProducts ?? []
            ]
        ));
    }

    public function getProducDescriptionData(Request $request)
    {
        $path = $request->path;
        $accountId = $this->getCurrentAccountId();
        $domain = AccountDomain::where('account_id', $accountId)->whereNotNull('type')->first();
        $saleChannel =  $domain ? $domain->type : 'preview';
        $appSaleChannel = new SaleChannel();
        $activeSaleChannels = $appSaleChannel->getActiveSaleChannels($accountId) ?? [];
        $product = UsersProduct::with(['variant_details', 'variants.values', 'product_option'])->where('account_id', $accountId)->where('path', $path)->firstOrFail();
        $product->saleChannels = $this->selectedSaleChannels($product);
        if (!in_array($saleChannel,  $product->saleChannels) || !in_array($saleChannel,  $activeSaleChannels) && $saleChannel !== 'preview') abort(404);
        $product->categories = $product->categories()->get()->map(function ($category) {
            return $category->id;
        });
        $product->productImageCollection = $this->imagesCollection(null, $product);
        $product->variants = $product->variants->map(function ($variant) {
            $variant->valueArray = $variant->values;
            return $variant;
        });

        if ($product->hasVariant) $product->variantPrice = $product->variant_details->min('price');
        $product->variant_details->map(function ($variantDetail) use ($product) {
            $product->productImageCollection = $this->imagesCollection($variantDetail->image_url, $product);
            $variantDetail->outOfStock = $variantDetail->quantity < 1 && !$variantDetail->is_selling || !$variantDetail->is_visible;
            $variantDetail->variations = $this->getVariations($product->variants, $variantDetail);
            return  $variantDetail;
        });

        $product->outOfStock = $product->hasVariant ? false : $product->quantity < 1 && !$product->is_selling;
        $product->customOptions =  $this->getProductOption($product);
        // $reviewSettings = ProductReviewSetting::ignoreAccountIdScope()->where('account_id', $accountId)->first();
        // $product->reviews = $this->reviews($accountId, $product, $reviewSettings);
        // $product->badgeLabel = $this->badgeLabel($product);
        $companyName = $this->companyName($accountId, $saleChannel);

        // $productRecommendationsTypes = $account->productRecommendationsTypes()->sortBy('priority')->map(function ($productRecommendation) {
        //     return $productRecommendation->type;
        // });

        // //order detail
        // if ($productRecommendationsTypes->contains('frequently-bought-together')) {
        //     $orders =
        //         DB::table('orders')->join('order_details', 'orders.id', '=', 'order_id')->whereUsersProductId($product->id)->select('orders.*',)->get()->map(function ($order) {
        //             return $order->id;
        //         });
        //     $frequentlyBoughtProducts = DB::table('order_details')
        //         ->whereIn('order_id', $orders)->select('users_product_id', DB::raw("SUM(quantity) as total_quantity"))->groupBy('users_product_id')
        //         ->orderByDesc('total_quantity')
        //         ->get()->where('users_product_id', '!==', $product->id)->map(function ($orderDetail) {
        //             return $orderDetail->users_product_id;
        //         });
        // }

        // return response()->json(['product' => $product, 'companyName' => $companyName, 'productRecommendationsTypes' => $productRecommendationsTypes, 'frequentlyBoughtProducts' => $frequentlyBoughtProducts ?? []]);
        return response()->json(
            [
                'products' => $this->getAllActiveProducts($accountId),
                'cartItems' => (new CheckoutRepository)::$cartItems ?? [],
                'selectedProduct' => $product,
                'companyName' => $companyName,
                'productRecommendationsTypes' => [],
                'frequentlyBoughtProducts' => $frequentlyBoughtProducts ?? []
            ]
        );
    }

    public function getVariations($variants, $variantDetail)
    {
        $variantValueIds = [];
        for ($i = 1; $i <= 5; $i++) {
            if ($variantDetail['option_' . $i . '_id'] != null) {
                array_push($variantValueIds, $variantDetail['option_' . $i . '_id']);
            }
        }
        $variations = [];
        $variantValue = $variants->flatMap(function ($variant) {
            return $variant->valueArray;
        });
        foreach ($variantValueIds as $variantValueId) {
            $variantValue = $variantValue->firstWhere('id', $variantValueId);
            $variant = $variants->firstWhere('id', $variantValue->variant_id);
            $label = $variant->display_name ?? $variant->variant_name;
            $value = $variantValue->variant_value;
            $variation = ['label' => $label, 'value' => $value];
            $variations[] = $variation;
        }
        return $variations;
    }

    public function variations($variantDetail)
    {
        $variantValueIds = [];
        for ($i = 1; $i <= 5; $i++) {
            if ($variantDetail['option_' . $i . '_id'] != null) {
                array_push($variantValueIds, $variantDetail['option_' . $i . '_id']);
            }
        }
        $variations = [];
        foreach ($variantValueIds as $variantValueId) {
            $variantValue = VariantValue::where('id', $variantValueId)->first();
            $variant = Variant::where('id', $variantValue->variant_id)->first();
            $label = $variant->display_name ?? $variant->variant_name;
            $value = $variantValue->variant_value;
            $variation = ['label' => $label, 'value' => $value];
            $variations[] = $variation;
        }
        return $variations;
    }
    public function imagesCollection($imageUrl, $product)
    {
        $images = $product->productImageCollection;
        if (!$images || count($images) < 1) $images = [$product->productImagePath];
        if ($images && !in_array($product->productImagePath, $images)) array_unshift($images, $product->productImagePath);
        if ($imageUrl && !in_array($imageUrl, $images)) array_push($images, $imageUrl);
        return $images;
    }
    public function companyName($accountId, $saleChannel)
    {
        return  $saleChannel === 'mini-store' ? MiniStore::where('account_id', $accountId)->first()->name : Account::whereId($accountId)->first()->company;
    }
    public function getRandomId($table, $type)
    {
        $condition = true;
        do {
            return $randomId = random_int(100000000001, 999999999999);
            $condition = DB::table($table)->where($type, $randomId)->exists();
        } while ($condition);
    }

    /**
     * Show shopping cart page 
     */
    public function shoppingCart()
    {
        $publishPageBaseData = $this->getPublishedPageBaseData();

        return Inertia::render('online-store/pages/ShoppingCart', $publishPageBaseData);
    }
}
