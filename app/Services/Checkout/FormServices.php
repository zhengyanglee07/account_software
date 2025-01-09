<?php

namespace App\Services\Checkout;

use App\EcommercePreferences;
use App\Order;

use App\Repository\CheckoutRepository;
use App\Repository\Checkout\CheckoutData;
use App\Repository\Checkout\FormDetail;

use App\Traits\AuthAccountTrait;


class FormServices
{
    use AuthAccountTrait;

    public $ecommercePreferences;
    public $deliveryMethod, $hasPhysicalProduct, $hasAddBillingAddress;

    public function __construct($deliveryMethod = null, $hasAddBillingAddress = null)
    {
        $this->ecommercePreferences = EcommercePreferences::firstWhere('account_id', $this->getCurrentAccountId());

        $this->hasPhysicalProduct = CheckoutRepository::$hasPhysicalProduct;

        $this->deliveryMethod = isset($deliveryMethod)
            ? $deliveryMethod
            : optional(CheckoutData::$shippingOption)->type;

        $this->hasAddBillingAddress = isset($hasAddBillingAddress)
            ? $hasAddBillingAddress
            : (CheckoutData::$formDetail->shipping->hasBillingAddress ?? false);
    }

    public function setPreferences($settings)
    {
        $this->ecommercePreferences->is_fullname = $this->isFieldRequired($settings['hasFullName'] ?? false);
        $this->ecommercePreferences->is_companyname = $this->isFieldRequired($settings['hasCompanyName'] ?? false);
        $this->ecommercePreferences->is_billingaddress = $this->isFieldRequired($settings['hasBillingAddress'] ?? false);
        $this->ecommercePreferences->checkout_method = ($settings['hasPhoneNumber'] ?? false) === 'true' ? 'email address&&mobile number' : 'email address';
    }

    public function isFullnameRequired(): bool
    {
        return $this->ecommercePreferences->is_fullname === 'required';
    }

    public function isShippingAddressRequired(): bool
    {
        return $this->hasPhysicalProduct && (!$this->hasDeliveryMethods() || $this->deliveryMethod !== 'pickup');
    }

    public function isShippingRequired(): bool
    {
        return $this->isShippingAddressRequired();
    }

    public function isBillingAddressRequired(): bool
    {
        return $this->ecommercePreferences->is_billingaddress === 'required';
    }

    public function isCompanyNameRequired(): bool
    {
        return $this->ecommercePreferences->is_companyname === 'required';
    }

    public function hasDeliveryMethods(): bool
    {
        return isset($this->deliveryMethod);
    }

    public function isEmailAddressRequired(): bool
    {
        $checkoutMethod = $this->ecommercePreferences->checkout_method;
        return str_contains($checkoutMethod, 'email address') && !$this->isEitherEmailOrMobileNumber();
    }

    public function isMobileNumberRequired(): bool
    {
        $checkoutMethod = $this->ecommercePreferences->checkout_method;
        return str_contains($checkoutMethod, 'mobile number') && !$this->isEitherEmailOrMobileNumber();
    }

    public function isEitherEmailOrMobileNumber(): bool
    {
        return str_contains($this->ecommercePreferences->checkout_method, '||');
    }

    public function isShippingOptionRequired(): bool
    {
        $hasDelivery = $this->ecommercePreferences->delivery_hour_type === 'custom';
        $hasPickup = $this->ecommercePreferences->is_enable_store_pickup;
        $isPickupOnly = !$hasDelivery && $hasPickup;

        $isRequired = $hasDelivery || $hasPickup;
        if ($isPickupOnly) $isRequired = $this->deliveryMethod === 'pickup';

        return $isRequired;
    }

    public function getFormAddress()
    {
        $formDetail = new FormDetail();
        $addressType = $this->isShippingAddressRequired() ? 'shipping' : 'billing';
        return $formDetail->{$addressType};
    }

    public function getFormDetailFromOrder(Order $order)
    {
        $formProperty = [
            'name',
            'company_name',
            'address',
            'city',
            'country',
            'state',
            'zipcode',
            'phoneNumber',
        ];
        $shipping = (object)[];
        $billing = (object)[];
        foreach ($formProperty as $key) {
            $shipping->{$key} = $order->{"shipping_$key"};
            if ($key !== 'phoneNumber')
                $billing->{$key} = $order->{"billing_$key"};
        }
        return [
            'shipping' => $shipping,
            'billing' => $billing,
        ];
    }

    private function isFieldRequired($isRequired)
    {
        return $isRequired && $isRequired === 'true' ? 'required' : 'hidden';
    }
}
