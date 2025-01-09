<?php

namespace App\Services\Checkout;

use App\UsersProduct;

use App\Repository\Checkout\CheckoutData;
use App\Repository\Checkout\FormDetail;
use App\Services\RedisService;
use App\Services\Checkout\PaymentService;
use App\Services\Checkout\ShippingService;
use App\Variant;
use App\VariantDetails;
use App\VariantValue;

class FunnelCheckoutService
{
    public function setFormDetail($formDetail)
    {
        $customerInfo = $formDetail->customerInfo;
        $shipping = $formDetail->shipping;
        $billing = $formDetail->billing;

        $data = request()->all();

        if ($data['hasFullName'] === 'false') {
            $customerInfo['fullName'] = FormDetail::DEFAULT_PROPERTY_VALUE;
        }
        if ($data['hasPhoneNumber'] === 'false') {
            $customerInfo['phoneNumber'] = FormDetail::DEFAULT_PROPERTY_VALUE;
        }
        if ($data['hasCompanyName'] === 'false') {
            $shipping['companyName'] = FormDetail::DEFAULT_PROPERTY_VALUE;
            $billing['companyName'] = FormDetail::DEFAULT_PROPERTY_VALUE;;
        }
        if ($data['hasBillingAddress'] === 'false') {
            foreach ($billing as $key => $value) {
                $billing[$key] = FormDetail::DEFAULT_PROPERTY_VALUE;
            }
        }

        RedisService::set('formDetail', [
            'customerInfo' => $customerInfo,
            'shipping' => $shipping,
            'billing' => $billing,
        ]);
    }

    public function setCartItems($productId)
    {
        $ids = explode('_', $productId);
        $userProduct = UsersProduct::find($ids[0]);
        if (!empty($ids[1])) {
            $variantDetails =  VariantDetails::find($ids[1]);
        }

        $hasVariant = isset($variantDetails);

        if ($hasVariant) {
            $variations = [];
            collect([1, 2, 3, 4, 5])->each(function ($index) use (&$variations, $variantDetails) {
                $valueId = $variantDetails['option_' . $index . '_id'];
                if (!$valueId) return;

                $variantValue = VariantValue::find($valueId);
                $variant = Variant::find($variantValue->variant_id);

                $variations[] = [
                    'id' => $variantDetails->id,
                    'label' => $variant->display_name ?? $variant->variant_name,
                    'value' => $variantValue->variant_value
                ];
            });
        }

        $cartItem = [
            'combinationIds' => [],
            'hasVariant' => $hasVariant,
            'qty' => 1,
            'reference_key' => $userProduct->reference_key,
            'tempId' => $userProduct->reference_key . ($variantDetails->reference_key ?? ''),
            'variantRefKey' => $variantDetails->reference_key ?? false,
            'variations' => $variations ?? [],

        ];
        RedisService::set('cartItems', [$cartItem]);

        return $userProduct;
    }

    public function setShippingMethod($userProduct)
    {
        $shippingService = new ShippingService();
        $isPhysicalProduct = $userProduct->type === 'physical';
        $manualShipping = $isPhysicalProduct ? array_values($shippingService->getManualShipping()) : [];
        $isRegionAvailable = count($manualShipping) > 0;
        if ($isPhysicalProduct && $isRegionAvailable) {
            $selectedShipping = $manualShipping[0];
            RedisService::set('shippingMethod', [
                'shipping_id' => $selectedShipping['id'],
                'shipping_method' => $selectedShipping['shipping_method'],
                'shipping_name' => $selectedShipping['shipping_name'],
            ]);
            new CheckoutData();
            CheckoutData::setSelectedShippingMethodDetail();
            RedisService::append('shippingMethod', 'charge', $shippingService->getShippingCharge());
        }
        return compact('isRegionAvailable', 'manualShipping');
    }

    public function setPaymentMethod()
    {
        $paymentMethod = collect((new PaymentService)->getCheckoutPayment())->first(function ($row) {
            return $row->name === PaymentService::PAYMENT_METHOD_STRIPE;
        });
        $isPaymentAvailable = isset($paymentMethod);

        if ($isPaymentAvailable) RedisService::set('paymentMethod', $paymentMethod->id, true);

        return ['paymentError' => $isPaymentAvailable ? null : 'No payment method available'];
    }
}
