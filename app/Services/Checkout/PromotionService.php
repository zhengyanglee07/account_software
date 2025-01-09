<?php

namespace App\Services\Checkout;

use App\Models\Promotion\Promotion;

use App\Repository\Checkout\CheckoutData;
use App\Repository\CheckoutRepository;
use App\Services\RedisService;
use App\Traits\AuthAccountTrait;

use Carbon\Carbon;

class PromotionService
{
    use AuthAccountTrait;

    public const PROMOTION_PRODUCT_DISCOUNT = 'product-discount';
    public const PROMOTION_SHIPPING_DISCOUNT = 'free-shipping';
    public const PROMOTION_ORDER_DISCOUNT = 'order-discount';

    public const PROMOTION_MANUAL_TYPE = 'manual';
    public const PROMOTION_AUTOMATIC_TYPE = 'automatic';

    protected $cartItems, $subTotal, $hasPhysicalProduct;
    public $availablePromotions, $basePromotionData;

    public function __construct()
    {
        $this->cartItems = CheckoutRepository::$cartItems;
        $this->subTotal = CheckoutRepository::$subTotalWithoutPromotion;
        $this->hasPhysicalProduct = CheckoutRepository::$hasPhysicalProduct;
        $this->basePromotionData = $this->getBasePromotionData();
        if (CheckoutRepository::$availablePromotions) {
            $this->availablePromotions = CheckoutRepository::$availablePromotions;
        } else {
            $this->setAvailablePromotion();
        }
    }

    public function setAvailablePromotion()
    {
        $this->availablePromotions = $this->getAllAvailablePromotion();
    }

    public function getPromotionDetail($id)
    {
        $promotion = Promotion::find($id);
        if (!isset($promotion)) return;

        $param = $this->basePromotionData;

        if ($promotion->discount_type === self::PROMOTION_PRODUCT_DISCOUNT) {
            $isValid = $promotion->checkValid($param);
            $discount['type'] = $promotion->promotion_method;
            $discount['category'] = $promotion->promotion_category;
            $discount['valid'] = $isValid;
            $appliedProductPromotion['promotionProducts'] = [];
            foreach ($param['productArray'] as $product) {
                $param['product'] = $product;
                if ($promotion->checkValidDiscountProduct($param)) {
                    array_push($appliedProductPromotion['promotionProducts'], $this->getProductPromotion($product, $promotion));
                }
            }

            $totalProductPrice = array_reduce($appliedProductPromotion['promotionProducts'], function ($acc, $item) {
                return $acc + $item['value'];
            });
            $discount['value'] = $totalProductPrice ?? 0;
            $appliedProductPromotion['promotion'] = $promotion->toArray();
            $appliedProductPromotion['valid_status'] = $isValid;
            $appliedProductPromotion['discountValue'] = $discount;
            $appliedPromotion = $appliedProductPromotion;
        } else {
            $appliedPromotion = $this->getGeneralPromotion($promotion);
        }
        return $appliedPromotion;
    }

    public function getValidAutomatedPromotion()
    {
        $automatedPromotion = Promotion::where('account_id', $this->getCurrentAccountId())
            ->where('promotion_method', 'automatic')
            ->where('promotion_status', 'active')
            ->whereDate('start_date', '<=', Carbon::now())
            ->orderBy('created_at', 'DESC')->get();

        $automatedDiscount =  [];

        $orderBasedPromotion = $automatedPromotion->where('promotion_category', 'Order');
        if ($orderBasedPromotion->isNotEmpty()) {
            $orderPromo = $this->getLatestAndNearestMinimumRequirement($orderBasedPromotion);
            if (isset($orderPromo)) $automatedDiscount[] = $this->getGeneralPromotion($orderPromo);
        }

        $shippingBasedPromotion = $automatedPromotion->where('promotion_category', 'Shipping');
        if ($shippingBasedPromotion->isNotEmpty()) {
            $shippingPromo = $this->getLatestAndNearestMinimumRequirement($shippingBasedPromotion);
            if (isset($shippingPromo)) $automatedDiscount[] = $this->getGeneralPromotion($shippingPromo);
        }

        $productBasedPromotion = $automatedPromotion->where('promotion_category', 'Product');
        if ($productBasedPromotion->isNotEmpty()) {
            $productPromo = $this->getValidProductPromotion($productBasedPromotion);
            if (isset($productPromo)) $automatedDiscount[] = $this->getPromotionDetail($productPromo->id);
        }

        return $automatedDiscount;
    }

    public function applyPromotion($discountCode)
    {
        $param = $this->basePromotionData;
        $promotion = Promotion::findByCode($discountCode, $this->getCurrentAccountId());
        $errorMessage = isset($promotion) ? $promotion->errorMessage($param) : 'Promo code is invalid';

        $response = ['error' => $errorMessage];
        if (isset($promotion)) {
            $response['data'] = $this->getPromotionDetail($promotion->id, $param);
        }
        return $response;
    }

    /**
     * Get valid manual applied promotion ids
     *
     * Only one (order / product) promotion and one free shipping allowed for one order
     *
     * @param  mixed $promotion
     * @return array
     */
    public function getAvailablePromotionIds($promotion): array
    {
        $promotions = [$promotion, ...CheckoutData::$promotion->toArray()];
        $validPromotion = [];
        foreach ($promotions as $promotion) {
            if (($promotion['promotion_method'] ?? '') === self::PROMOTION_AUTOMATIC_TYPE) continue;
            $sameGroupPromotion = array_filter($validPromotion ?? [], function ($validPromo) use ($promotion) {
                return $this->isSamePromotionGroup(
                    $promotion['discount_type'] ?? $promotion['promotion']['discount_type'],
                    $validPromo['promotion']['discount_type'] ?? '',
                );
            });
            if (!isset(array_values($sameGroupPromotion)[0])) $validPromotion[] = $promotion;
        }
        $availablePromotionIds = array_map(function ($promo) {
            return $promo['id'] ?? $promo['promotion']['id'];
        }, $validPromotion);
        return $availablePromotionIds;
    }

    /**
     * Get all applied promotion or valid automation promotion
     *
     * @param  mixed $isIncludeInvalid
     *   Get invalid promotion before remove, used for display error to client once if invalid
     * @return array
     */
    public function getAllAvailablePromotion($isIncludeInvalid = false)
    {
        $automatedDiscount = CheckoutRepository::$isFunnel ? [] : $this->getValidAutomatedPromotion();
        $manualDiscount = [];
        CheckoutData::$promotion->each(function ($promotion) use (&$manualDiscount) {
            $manualDiscount[] = $this->getPromotionDetail($promotion->id);
        });

        $allValidPromotions = array_merge($manualDiscount, $automatedDiscount);

        $validPromotions = [];
        $validPromotionIds = [];
        $invalidPromotions = [];
        foreach ($allValidPromotions as $promotion) {
            $sameGroupPromotion = array_filter($validPromotions ?? [], function ($validPromo) use ($promotion) {
                return $this->isSamePromotionGroup(
                    $promotion['promotion']['discount_type'],
                    $validPromo['promotion']['discount_type']
                );
            });
            if (!isset(array_values($sameGroupPromotion)[0]) && $promotion['valid_status']) {

                $validPromotions[] = $promotion;
                $validPromotionIds[] = $promotion['promotion']['id'];
            }
            if ($isIncludeInvalid && !$promotion['valid_status']) $invalidPromotions[] = $promotion;
        }

        RedisService::set('promotion', $validPromotionIds);

        $promotionList = $validPromotions;
        if ($isIncludeInvalid) $promotionList = array_merge($validPromotions, $invalidPromotions);
        return $promotionList;
    }

    public function getAvailablePromotionByCategory($category)
    {
        $promotions = array_filter($this->availablePromotions, function ($promotion)  use ($category) {
            return $promotion['promotion']['discount_type'] === $category;
        });
        return array_values($promotions);
    }

    public function checkIsPromoCodeApplied($promoCode)
    {
        return CheckoutData::$promotion->contains(function ($value) use ($promoCode) {
            return $value->discount_code === $promoCode;
        });
    }

    public function hasActivePromotion($includeAutomatedPromo = true)
    {
        $contrains = ['promotion_status' => 'active'];
        if (!$includeAutomatedPromo) $contrains['promotion_method'] = 'manual';
        return Promotion::where('account_id', $this->getCurrentAccountId())
            ->where($contrains)
            ->whereDate('start_date', '<=', Carbon::now())
            ->exists();
    }

    public function getGroupedPromotion()
    {
        return  [
            'order' => $this->getAvailablePromotionByCategory(self::PROMOTION_ORDER_DISCOUNT),
            'product' => $this->getAvailablePromotionByCategory(self::PROMOTION_PRODUCT_DISCOUNT),
            'shipping' => $this->getAvailablePromotionByCategory(self::PROMOTION_SHIPPING_DISCOUNT),
        ];
    }

    private function isSamePromotionGroup($type1, $type2)
    {
        return $type1 === self::PROMOTION_SHIPPING_DISCOUNT
            ? $type1 === $type2
            : in_array($type2, [self::PROMOTION_PRODUCT_DISCOUNT, self::PROMOTION_ORDER_DISCOUNT]);
    }

    private function getLatestAndNearestMinimumRequirement($promotionArr)
    {
        $param = $this->basePromotionData;
        $nearestValue = 0;
        $filteredPromo = null;
        foreach ($promotionArr as $promotion) {
            if ($promotion->checkValid($param)) {
                $difference = abs($promotion->promotionType['requirement_value'] - $param['totalAfterDiscount']);
                if ($nearestValue == 0) {
                    $nearestValue = $difference;
                }
                if ($difference <= $nearestValue) {
                    $nearestValue = $difference;
                    $filteredPromo = $promotion;
                }
            }
        }
        return $filteredPromo;
    }

    private function getValidProductPromotion($promotions)
    {
        $validPromotion = [];
        foreach ($promotions as $promotion) {
            if ($promotion->checkValid($this->basePromotionData)) {
                array_push($validPromotion, $promotion);
            }
        }
        return $validPromotion[0] ?? null;
    }

    private function getBasePromotionData()
    {
        $formDetail = CheckoutData::$formDetail;
        return [
            'totalAfterDiscount' => $this->subTotal,
            'subTotal' =>  $this->subTotal,
            'productArray' => $this->cartItems,
            'customerEmail' => $formDetail->customerInfo->email,
            'country' => $formDetail->shipping->country,
            'customerInfo' => $formDetail,
            'isPhysicalProduct' => $this->hasPhysicalProduct,
            'currencyInfo' => CheckoutData::$currency,
        ];
    }


    private function getProductPromotion($product, $promotion)
    {
        $details['id'] = $product->id;
        $details['hasVariant'] = $product->hasVariant;
        $details['variantCombinationID'] = $product->tempId;
        $details['value'] = floatVal($promotion->discount(($product->price * $product->qty), CheckoutData::$currency));
        $valueAfterDiscount = ($product->price * $product->qty) - $details['value'];
        $details['valueAfterDiscount'] = $valueAfterDiscount <= 0 ? 0 : $valueAfterDiscount;
        $details['promotion'] = $promotion->toArray();
        return $details;
    }
    private function getGeneralPromotion($promotion)
    {
        $param = $this->basePromotionData;
        $isValid = $promotion->checkValid($param);
        $selectedShipping = CheckoutData::$shippingMethod;
        $shippingCharge =  $selectedShipping->selectedShipping ?? 0;
        $discount['value'] = floatVal($promotion->discount($this->subTotal, CheckoutData::$currency, $shippingCharge));
        $discount['type'] = $promotion->promotion_method === 'manual' ?: 'automated';
        $discount['category'] = $promotion->promotion_category;
        $discount['valid'] = $isValid;
        $appliedPromotion['promotion'] = $promotion->toArray();
        $appliedPromotion['discountValue'] = $discount;
        $appliedPromotion['valid_status'] = $isValid;
        return $appliedPromotion;
    }
}
