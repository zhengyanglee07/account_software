<?php

namespace App\Repository\Checkout;

use App\Services\Checkout\CustomerAccountService;
use App\Services\Checkout\FormServices;
use App\Services\RedisService;

use App\Traits\SalesChannelTrait;

class FormDetail
{
    use SalesChannelTrait;

    public const DEFAULT_PROPERTY_VALUE = null;

    public $customerInfo;
    public $shipping;
    public $billing;

    protected $manualFormDetail, $formDetail, $customerAccount, $shippingAddress;

    public function __construct($manualFormDetail = null)
    {
        $this->manualFormDetail = $manualFormDetail;

        $this->setFormDetail();

        return (object)[
            'customerInfo' => $this->customerInfo,
            'shipping' => $this->shipping,
            'billing' => $this->billing,
        ];
    }
    /**
     * Priority: Manual Form Detail -> Form Detail -> Customer Account -> Location Modal
     *
     * @return void
     */
    private function setFormDetail()
    {
        $this->formDetail = $this->getStoredFormDetail();
        $this->customerAccount = $this->getCustomerAccountDetail();
        $this->shippingAddress = $this->getStoredShippingAddress();

        $this->customerInfo = (object)[
            'fullName' => $this->getCustomerInfo('fullName'),
            'email' => $this->getCustomerInfo('email'),
            'phoneNumber' => $this->getCustomerInfo('phoneNumber'),
        ];

        $this->shipping = (object)[
            'fullName' => $this->getShippingDetail('fullName'),
            'companyName' => $this->getShippingDetail('companyName'),
            'address' => $this->getShippingDetail('address'),
            'city' => $this->getShippingDetail('city'),
            'country' => $this->getShippingDetail('country'),
            'state' => $this->getShippingDetail('state'),
            'zipCode' => $this->getShippingDetail('zipCode'),
            'phoneNumber' => $this->getShippingDetail('phoneNumber'),
            'hasBillingAddress' => $this->getShippingDetail('hasBillingAddress'),
        ];

        $this->billing = (object)[
            'fullName' => $this->getBillingDetail('fullName'),
            'companyName' => $this->getBillingDetail('companyName'),
            'address' => $this->getBillingDetail('address'),
            'city' => $this->getBillingDetail('city'),
            'country' => $this->getBillingDetail('country'),
            'state' => $this->getBillingDetail('state'),
            'zipCode' => $this->getBillingDetail('zipCode'),
        ];

        $this->removeNonRequiredField();
    }

    private function removeNonRequiredField()
    {
        $currentUrl = request()->url();
        $excludedPath = ['checkout/data/twostepform'];
        $isCheckout = str_contains($currentUrl, 'checkout');
        $isExcludedPath = array_reduce($excludedPath, function ($acc, $path) use ($currentUrl) {
            return $acc || (str_contains($currentUrl, $path));
        }, false);

        if (!$isCheckout || $isExcludedPath) return;

        $isCheckout = str_contains($currentUrl, 'checkout');
        $formService = new FormServices();

        if (!$formService->isFullnameRequired()) {
            $this->customerInfo->fullName = self::DEFAULT_PROPERTY_VALUE;
        }
        if (!$formService->isEmailAddressRequired() && !$formService->isEitherEmailOrMobileNumber()) {
            $this->customerInfo->email = self::DEFAULT_PROPERTY_VALUE;
        }

        if (!$formService->isMobileNumberRequired() && !$formService->isEitherEmailOrMobileNumber()) {
            $this->customerInfo->phoneNumber = self::DEFAULT_PROPERTY_VALUE;
        }
        if (!$formService->isCompanyNameRequired()) {
            $this->shipping->companyName = self::DEFAULT_PROPERTY_VALUE;
            $this->billing->companyName = self::DEFAULT_PROPERTY_VALUE;
        }
        if (!$formService->isBillingAddressRequired()) {
            foreach ($this->billing as $key => $value) {
                $this->billing->{$key} = self::DEFAULT_PROPERTY_VALUE;
            }
        }
    }

    private function getStoredFormDetail()
    {
        return RedisService::get('formDetail');
    }

    /**
     * Shipping address filled in location modal
     *
     * @return void
     */
    private function getStoredShippingAddress()
    {
        return $this->checkIsCurrentSalesChannel('mini-store')
            ? RedisService::get('shippingAddress')
            : null;
    }

    private function getCustomerAccountDetail()
    {
        $customerAccount = (new CustomerAccountService)->getCustomerAccountDetail();
        if (!isset($customerAccount)) return;

        $processedContact = $customerAccount['processedContact'];
        $address = $customerAccount['address'];
        return [
            'customerInfo' => [
                'fullName' => $processedContact->fname,
                'email' => $processedContact->email,
                'phoneNumber' => $processedContact->phone_number,
            ],
            'shipping' => [
                'fullName' => $address->shipping_name,
                'companyName' => $address->shipping_company_name,
                'address' => $address->shipping_address,
                'city' => $address->shipping_city,
                'country' => $address->shipping_country,
                'state' => $address->shipping_state,
                'zipCode' => $address->shipping_zipcode,
                'phoneNumber' => $address->shipping_phoneNumber,
            ],
            'billing' => [
                'fullName' => $address->billing_name,
                'companyName' => $address->billing_company_name,
                'address' => $address->billing_address,
                'city' => $address->billing_city,
                'country' => $address->billing_country,
                'state' => $address->billing_state,
                'zipCode' => $address->billing_zipcode,
            ],
        ];
    }

    private function getCustomerInfo($property)
    {
        if (!empty($this->customerAccount['customerInfo'][$property]))
            return $this->customerAccount['customerInfo'][$property];
        if (!empty($this->manualFormDetail['customerInfo'][$property]))
            return $this->manualFormDetail['customerInfo'][$property];
        if (!empty($this->formDetail['customerInfo'][$property]))
            return $this->formDetail['customerInfo'][$property];
        return self::DEFAULT_PROPERTY_VALUE;
    }

    private function getShippingDetail($property)
    {
        if (!empty($this->manualFormDetail['shipping'][$property]))
            return $this->manualFormDetail['shipping'][$property];
        if (!empty($this->formDetail['shipping'][$property]))
            return $this->formDetail['shipping'][$property];
        if (!empty($this->customerAccount['shipping'][$property]))
            return $this->customerAccount['shipping'][$property];
        if (!empty($this->shippingAddress[$property]))
            return $this->shippingAddress[$property];
        return self::DEFAULT_PROPERTY_VALUE;
    }

    private function getBillingDetail($property)
    {
        if (!empty($this->manualFormDetail['billing'][$property]))
            return $this->manualFormDetail['billing'][$property];
        if (!empty($this->formDetail['billing'][$property]))
            return $this->formDetail['billing'][$property];
        if (!empty($this->customerAccount['billing'][$property]))
            return $this->customerAccount['billing'][$property];
        return self::DEFAULT_PROPERTY_VALUE;
    }
}
