<?php

namespace App\Traits\Checkout;

use App\Order;

use App\Repository\CheckoutRepository;
use App\Repository\Checkout\CheckoutData;

use App\Services\Checkout\FormServices;
use App\Services\Checkout\TaxService;

use App\Traits\CurrencyConversionTraits;

use Illuminate\Support\Collection;


trait CheckoutOrderTrait
{
    use  CurrencyConversionTraits;

    public function getCustomOrders($columns = ['*']): Collection
    {
        return Order::where('account_id', $this->getCurrentAccountId())
            ->where('delivery_hour_type', 'custom')
            ->get($columns);
    }


    /**
     * Calculate subtotal of checkout order
     *
     * Subtotal = total net price of cart items
     *
     * @return float
     */
    public function calculateSubtotal($hasProductDiscount = true): float
    {
        $cartItems = CheckoutRepository::${$hasProductDiscount ? 'cartItemsWithPromotion' : 'cartItems'};
        return array_reduce($cartItems, function ($total, $item) {
            return $total + ((float)$item->netPrice * (int)$item->qty);
        }, 0);
    }


    /**
     * Calculate grandtotal of checkout order
     *
     * Grandtotal = subtotal + shipping fee - promotion (order/shipping) - store credit
     *
     * @return float
     */
    public function calculateGrandTotal()
    {
        $groupedPromotions = CheckoutRepository::$groupedPromotions;
        $orderDiscountTotal = array_reduce($groupedPromotions['order'], function ($total, $discount) {
            return $total + $discount['discountValue']['value'];
        }, 0);

        $hasShippingDiscount = count($groupedPromotions['shipping']);

        $formService = new FormServices();
        $shippingFee = ($formService->isShippingRequired() && !$hasShippingDiscount) ? (optional(CheckoutData::$shippingMethod)->charge ?? 0) : 0;

        $tax = (new TaxService)->getTotalTax($shippingFee, CheckoutRepository::$taxableProductTotal, true);

        $total = (float)$this->calculateSubtotal() + (float)$shippingFee + (float)$tax - (float)$orderDiscountTotal;

        return $this->priceFormater($total, CheckoutData::$currency, false);
    }
}
