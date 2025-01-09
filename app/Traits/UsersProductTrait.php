<?php

namespace App\Traits;

use App\Order;
use App\Category;
use App\UsersProduct;
use App\OrderDetail;
use App\ProductReview;
use App\ProductReviewSetting;
use App\productOptionValue;
use App\Repository\CheckoutRepository;
use App\Traits\CurrencyConversionTraits;
use App\Variant;
use App\VariantValue;
use Illuminate\Support\Facades\DB;

trait UsersProductTrait
{
    use CurrencyConversionTraits;

    public function getProductOption($product)
    {
        $productOptions = $product->product_option;
        $productOptionArray = [];
        foreach ($productOptions as $productOption) {
            $productOptionValue = $productOption->values;
            $productOption = [
                "id" => $productOption->id,
                "display_name" => $productOption->display_name,
                "type" => $productOption->type,
                "help_text" => $productOption->help_text,
                "tool_tips" => $productOption->tool_tips,
                "is_range" => $productOption->is_range,
                "up_to" => ($productOption->is_range == 0) ? $productOptionValue->count() + 1 : $productOption->up_to,
                "at_least" => ($productOption->is_range == 0) ? 0 : $productOption->at_least,
                "is_required" => $productOption->is_required,
                'is_total_Charge' => $productOption->is_total_Charge,
                'global_priority' => $productOption->global_priority,
                'total_charge_amount' => $productOption->total_charge_amount,
                'inputs' => $productOptionValue,
            ];
            array_push($productOptionArray, $productOption);
        }
        return $productOptionArray;
    }
    public function productSaleQuantity($productId, $orders)
    {
        return DB::table('order_details')->whereIn('order_id', $orders->pluck('id'))
            ->where('users_product_id', $productId)
            ->get()->sum('quantity');
    }
    public function selectedSaleChannels($product)
    {
        $selectedSaleChannels = [];
        $productSaleChannels = UsersProduct::find($product->id)->saleChannels()->get();
        foreach ($productSaleChannels as $saleChannel) {
            $selectedSaleChannels[] = $saleChannel->type;
        };
        if ($product->status === 'active' && count($productSaleChannels) === 0) $selectedSaleChannels = ['funnel', 'online-store', 'mini-store'];
        return $selectedSaleChannels;
    }

    // all categories & active products
    public function getAllCategoriesWithActiveProducts($accountId)
    {
        // $currency = $this->getCurrency($accountId);
        $allCategory =  Category::where('account_id', $accountId)->with(array('products' => function ($query) {
            $query->where('status', 'active');
        }))->orderBy('priority')->get()->map(function ($category) {
            $category->productsId = $this->getCategoriesProductsId($category);
            return $category;
        });
        // foreach ($allCategory as $category) {
        //     $category->products->map(function ($product) use ($accountId, $currency, $orders) {
        //         $product->productComparePrice = $this->priceFormater($product->productComparePrice, $currency);
        //         $product->productPrice = $this->priceFormater($product->productPrice, $currency);
        //         $product->variantPrice =  $this->priceFormater($this->variantPrice($product), $currency);
        //         $product->custom_options =  $this->getProductOption($product);
        //         $product->reviews = $this->reviews($accountId, $product->id);
        //         // $product->badgeLabel = $this->badgeLabel($product);
        //         $product->saleQuantity = $this->productSaleQuantity($product->id, $accountId, $orders);
        //         $product->saleChannels = $this->selectedSaleChannels($product);
        //         return $product;
        //     });
        // }
        return $allCategory;
    }

    public function getCategoriesProductsId($category)
    {
        $productCategories = DB::table('product_category')->where('category_id', $category->id)->get();
        $productsIdArray = [];
        foreach ($productCategories as $productCategory) {
            $productsIdArray[] = $productCategory->product_id;
        };
        return $productsIdArray;
    }


    public function getAllActiveProducts($accountId, $isIgnoreStatus = false)
    {
        $currency = $this->getCurrency($accountId);
        if ($accountId === null) $accountId = $this->getCurrentAccountId();
        $constrains = ['account_id' => $accountId, 'deleted_at' => null];
        if (!$isIgnoreStatus) $constrains['status'] = 'active';
        $allProducts = CheckoutRepository::$allProducts;
        if (isset($allProducts) && count($allProducts)) return $allProducts;
        return UsersProduct::with('variant_details', 'product_option.values', 'saleChannels')->withSum('orderDetails', 'quantity')->where($constrains)->get()->map(function ($product) use ($currency) {
            $product->variantPrice = $product->orderBy('price')->first()->price;
            $product->comparePrice = $this->priceFormater($product->productComparePrice, $currency);
            $product->originalPrice = $product->productPrice;
            $product->price = $this->priceFormater($product->productPrice, $currency);
            $product->variantPrice =  $this->priceFormater($product->variantPrice, $currency);
            $product->custom_options =  $this->getProductOption($product);
            // $product->reviews = $this->reviews($accountId, $product, $reviewSettings);
            // $product->badgeLabel = $this->badgeLabel($product);
            $product->saleQuantity =  $product->orderDetails_quantity;
            $product->saleChannels = $product->saleChannels()->pluck('type')->toArray();
            return $product;
        });
    }
    public function variantProduct($product)
    {
        $variantDetails = $product->variant_details;
        $variantPrice = null;
        if ($product->hasVariant) $variantPrice = $variantDetails?->first()?->price;
        foreach ($variantDetails as $variantDetail) {
            if ($variantPrice !== $variantDetail->price) {
                $variantPrice =
                    $variantPrice < $variantDetail->price
                    ? $variantPrice
                    : $variantDetail->price;
            }
        }
        $product->variantPrice = $variantPrice;
        return $product;
    }
    public function reviews($accountId, $product, $reviewSettings)
    {
        //TO DO: Reefactor to more simpler code logic
        $reviews = ProductReview::ignoreAccountIdScope()->where(['account_id' => $accountId, 'status' => 'published', 'product_id' => $product->id])->get();
        $displayReview = $reviewSettings !== null ? $reviewSettings->display_review : 1;
        $totalRate = 0;
        $avgRate = 0;
        foreach ($reviews as $review) {
            $totalRate += $review->star_rating;
            $review->productTitle = $product->productTitle;
        }
        if ($totalRate !== 0) $avgRate = $totalRate / count($reviews);
        $starWidth = intval(($avgRate - intval($avgRate)) * 100);
        $reviews = [
            'displayReview' => $displayReview,
            'reviewSettings' => $reviewSettings,
            'avgRate' => $avgRate,
            'starWidth' => $starWidth,
            'reviewCount' => count($reviews),
        ];
        return $reviews;
    }
    public function badgeLabel($product)
    {
        return UsersProduct::find($product->id)->badges()->orderBy('priority', 'asc')->first();
    }
    public function getCurrency($accountId)
    {
        return DB::table('currencies')->where('account_id', $accountId)->where('isDefault', 1)->first();
    }

    public function getCombinationIds($variantDetail)
    {
        $variantValueIds = [];
        for ($i = 1; $i <= 5; $i++) {
            if ($variantDetail->{'option_' . $i . '_id'} != null) {
                array_push($variantValueIds, $variantDetail->{'option_' . $i . '_id'});
            }
        }
        return $variantValueIds;
    }

    public function getVariantValue($variantDetail)
    {
        $variantValueIds = $this->getCombinationIds($variantDetail);
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

    public function setVariantLabel(&$products)
    {
        $products->each(function ($product) {
            $variantLabels  = [];
            $product->variant_values->each(function ($value) use (&$variantLabels) {
                $variantLabels[$value->id] = $value->variant_value;
            });
            $product->variant_details->map(function ($detail) use ($variantLabels) {
                $labels = [];
                collect([1, 2, 3, 4, 5])->each(function ($index) use (&$labels, $variantLabels, $detail) {
                    if (!empty($detail->{'option_' . $index . '_id'})) {
                        $labels[] = $variantLabels[$detail->{'option_' . $index . '_id'}];
                    }
                });
                $detail->label = join(' / ', $labels);
            });
        });
    }

    public function setSoldQuantity($accountId, &$products)
    {
        $orderIds = Order::where('account_id', $accountId)->pluck('id');
        $orderDetails
            = DB::table('order_details')->whereIn('order_id', $orderIds);

        $products->each(function ($product) use ($orderDetails) {
            $product->soldQuantity =
                $orderDetails->where('users_product_id', $product->id)->sum('quantity');
        });
    }
}
