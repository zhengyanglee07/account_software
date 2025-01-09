<?php

namespace App\Services\Checkout;

use App\Models\AccessList;
use App\Models\CourseStudent;
use App\Order;
use App\UsersProduct;

use App\Repository\Checkout\CheckoutData;
use App\Repository\CheckoutRepository;
use App\Traits\UsersProductTrait;
use App\Traits\SalesChannelTrait;

class CartService
{
    use UsersProductTrait, SalesChannelTrait;

    public $cartItems, $cartItemFromRedis;

    public function __construct()
    {
        $this->cartItemFromRedis = CheckoutData::$cartItems ?? [];
        $this->cartItems = CheckoutRepository::$cartItems ?? $this->getCartItems();
    }

    public function setCartItems()
    {
        $this->cartItems = $this->getCartItems();
    }

    public function hasPhysicalProduct()
    {
        $referenceKeys = $this->cartItemFromRedis->pluck('reference_key');
        return UsersProduct::whereIn('reference_key', $referenceKeys)
            ->where('type', 'physical')->exists();
    }

    public function getCartItemDetail()
    {
        $referenceKeys = $this->cartItemFromRedis->pluck('reference_key');
        return $this->getAllActiveProducts($this->getCurrentAccountId(), true)->whereIn('reference_key', $referenceKeys)->all();
    }

    public function getCartItems()
    {
        $currency = (new CheckoutData)::$currency;
        $allProducts = $this->getCartItemDetail();
        $selectedProduct = [];
        foreach ($this->cartItemFromRedis as $product) {
            $tempProduct = [];
            $item = array_filter($allProducts, function ($el) use ($product) {
                return $el->reference_key == $product['reference_key'];
            });
            $item = array_values($item);
            if (!isset($item[0])) continue;
            $item = $item[0];
            $tempProduct = (object)array_merge((array)$product, $item->toArray());
            $selectedVariantDetail = collect($item->variant_details)->where('reference_key', $product['variantRefKey'])->first();
            $selectedCustomOptions = array_filter($item->custom_options, function ($c1) use ($product) {
                return array_reduce($product['customizations'] ?? [], function ($isSelected, $c2) use ($c1) {
                    return $isSelected || $c1['id'] === $c2['id'];
                }, false);
            });
            $selectedCustomOptions = array_values($selectedCustomOptions);
            $selectedCustomizations = array_filter((array)optional($product)['customizations'], function ($c1) use ($item) {
                return array_reduce($item->custom_options, function ($isSelected, $c2) use ($c1) {
                    return $isSelected || $c1['id'] === $c2['id'];
                }, false);
            });

            $customizationsArray = null;
            $customizationPrice = 0;

            foreach ($selectedCustomOptions as $index => $custom) {
                $customizationsArray =  $custom['inputs']->filter(function ($c1) use ($selectedCustomizations, $index) {
                    return array_reduce($selectedCustomizations[$index]['values'], function ($isSelected, $c2) use ($c1) {
                        return $isSelected || ($c1['id'] === $c2['id'] && !$c1->is_total_Charge);
                    }, false);
                });
                if ($custom['is_total_Charge'] && count($customizationsArray) > 0) {
                    $customizationPrice += $custom['total_charge_amount'];
                } else {
                    $customizationPrice += $customizationsArray->reduce(function ($total, $c) {
                        return $total + $c['single_charge'];
                    }, 0);
                }
            }
            $item->formattedPrice = $this->priceFormater($item->productPrice, $currency, false);
            $variantPrice = isset($selectedVariantDetail) ? (float)$selectedVariantDetail->price : null;
            $tempProduct->productImagePath = isset($selectedVariantDetail) ? $selectedVariantDetail->image_url : $tempProduct->productImagePath;
            $tempProduct->price = (float)($variantPrice ?? $item->formattedPrice) + $customizationPrice;
            $tempProduct->netPrice = $tempProduct->price;
            $tempProduct->customizations = array_map(function ($cus) use ($item) {
                $data =  [
                    'id' => $cus['id'],
                    'index' => $cus['index'],
                    'label' => $cus['label'],
                    'values' => $cus['values'],
                ];
                $customOption = array_filter($item->custom_options, function ($option) use ($cus) {
                    return $option['id'] === $cus['id'];
                });
                $customOption = array_values($customOption);

                if (!isset($customOption[0])) return $data;
                $customOption = (object)$customOption[0];
                return [
                    ...$data,
                    'is_total_charge' => $customOption->is_total_Charge,
                    'total_charge_amount' => $customOption->total_charge_amount,
                    'values' => array_map(function ($val) use ($customOption) {
                        $selectedOption = array_filter((array)$customOption->inputs->all(), function ($e) use ($val) {
                            return $e['id'] === $val['id'];
                        });
                        $selectedOption = array_values($selectedOption);
                        $val['single_charge'] = 0;
                        if (isset($selectedOption[0]))
                            $val['single_charge'] = $selectedOption[0]['single_charge'];
                        return $val;
                    }, (array)$cus['values'])

                ];
            }, optional($product)['customizations'] ?? []);
            array_push($selectedProduct, $tempProduct);
        }
        return $selectedProduct;
    }

    public function getCartItemWithPromotion($promotions = [])
    {
        return array_map(function ($cartItem) use ($promotions) {
            $cartItem->discount = [];
            $cartItem->isDiscountApplied = false;

            if (!isset($promotions[0])) return $cartItem;

            foreach ($promotions[0]['promotionProducts'] as $promotion) {
                if ($cartItem->id !== $promotion['id']) continue;
                if (!$cartItem->hasVariant || $cartItem->tempId === $promotion['variantCombinationID']) {
                    $cartItem->discount = $promotion;
                    $cartItem->isDiscountApplied = true;
                    $cartItem->netPrice = (float)$promotion['valueAfterDiscount'] / (int)$cartItem->qty;
                }
            }
            return $cartItem;
        }, $this->cartItems);
    }

    public static function getCartItemsFromOrder(Order $order)
    {
        $cartItems = [];
        $order->orderDetails->each(function ($orderDetail) use ($order, &$cartItems) {
            $product = UsersProduct::with('variant_details')->find($orderDetail->users_product_id);
            $variations = json_decode($orderDetail->variant, true);

            $isActive = $product->type === 'physical' ||
                ($product->type === 'course' ?  CourseStudent::class : AccessList::class)::where([
                    'product_id' => $orderDetail->users_product_id,
                    'processed_contact_id' => $order->processed_contact_id,
                    'is_active' => 1,
                ])->exists();

            $cartItems[] = [
                ...$product->toArray(),
                'customizations' => json_decode($orderDetail->customization, true),
                'variations' => $variations,
                'variantRefKey' => $product->variant_details->find($variations[0]['id'] ?? 0)?->reference_key,
                'qty' => $orderDetail->quantity,
                'netPrice' => $orderDetail->unit_price,
                'price' => $orderDetail->unit_price,
                'productPrice' => $orderDetail->unit_price,
                'productImagePath' => $orderDetail->image_url,
                'isDiscountApplied' => $orderDetail->is_discount_applied,
                'discount' => json_decode($orderDetail->discount_details, true),
                'isFulfilled' => $orderDetail->fulfillment_status === CheckoutOrderService::ORDER_FULFILLED_STATUS && $isActive,
            ];
        });
        return $cartItems;
    }

    public function getCartItemVariant($item)
    {
        return collect($item->variant_details)
            ->firstWhere(
                'reference_key',
                $item->variantRefKey
            );
    }

    public function getCartItemVariantName($variant)
    {
        return array_reduce($variant, function ($name, $item) {
            return $name . '/' . $item['value'];
        }, '');
    }

    public function getTotalWeight(): float
    {
        return (float)CheckoutData::$cartItems->reduce(function ($total, $cartItem) {
            $usersProduct = UsersProduct::with('variant_details')->firstWhere('reference_key', $cartItem['reference_key']);
            if ($cartItem['hasVariant']) {
                $variantDetail = $usersProduct->variant_details->firstWhere('reference_key', $cartItem['variantRefKey']);
                return $total + ($variantDetail->weight ?? 0) * $cartItem['qty'];
            }
            return $total + $usersProduct->weight * $cartItem['qty'];
        });
    }

    public function isPhysicalProduct($cartItem)
    {
        return $cartItem->type === 'physical';
    }

    public function getTotalTaxableProductPrice($cartItems)
    {
        return array_reduce($cartItems, function ($total, $cartItem) {
            return $total + ($cartItem->isTaxable ? ((float)$cartItem->netPrice * (int)$cartItem->qty) : 0);
        });
    }

    public function getProductOfCurrentSalesChannel($cartItems)
    {
        $cartItems =  collect($cartItems);
        $cartItems = $cartItems->filter(function ($item) {
            return collect($item->saleChannels)->contains($this->getCurrentSalesChannel());
        });
        return $cartItems->all();
    }
}
