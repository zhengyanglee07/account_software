<?php

namespace App\Services\Checkout;

use App\PaymentAPI;
use App\Repository\Checkout\CheckoutData;
use App\Services\Checkout\Payment\Ipay88Service;
use App\Services\Checkout\Payment\StripeService;
use App\Traits\CurrencyConversionTraits;
use App\Traits\AuthAccountTrait;
use App\Traits\Checkout\CheckoutCurrencyTrait;
use App\Traits\Checkout\CheckoutOrderTrait;

class PaymentService
{
    use AuthAccountTrait, CurrencyConversionTraits, CheckoutCurrencyTrait, CheckoutOrderTrait;

    public const PAYMENT_UNPAID_STATUS = 'Unpaid';
    public const PAYMENT_PARTIALLY_PAID_STATUS = 'Partially Paid';
    public const PAYMENT_PAID_STATUS = 'Paid';
    public const PAYMENT_PARTIALLY_REFUNDED_STATUS = 'Partially Refunded';
    public const PAYMENT_REFUNDED_STATUS = 'Refunded';

    public const PAYMENT_PROCESS_PENDING_STATUS = 'Pending';
    public const PAYMENT_PROCESS_SUCCESS_STATUS = 'Success';
    public const PAYMENT_PROCESS_FAILED_STATUS = 'Failed';

    // Payment methods name
    public const PAYMENT_METHOD_MANUAL = 'manual payment';
    public const PAYMENT_METHOD_STRIPE = 'stripe';
    public const PAYMENT_METHOD_STRIPE_FPX = 'stripe FPX';
    public const PAYMENT_METHOD_SENANG_PAY = 'senangPay';
    public const PAYMENT_METHOD_IPAY88 = 'ipay88';
    public const PAYMENT_METHOD_STORE_CREDIT = 'store credit';
    public const PAYMENT_METHOD_NONE = 'none';

    /**
     * Get enabled payment methods used on checkout pages
     *
     * @param  mixed $isSubscription
     * @return object
     */
    public function getCheckoutPayment($isSubscription = false): array
    {
        $accountId = $this->getCurrentAccountId();
        $payment_gateways = PaymentAPI::Where('account_id', $accountId)->get();
        $payment_methods = [];

        if ($isSubscription) {
            return $this->getSubscription($accountId);
        }

        foreach ($payment_gateways as $key => $payment_gateway) {
            if (!$payment_gateway->enabled_at) continue;
            $isStripePayment = $payment_gateway->payment_methods === self::PAYMENT_METHOD_STRIPE;
            $isIpay88Payment = $payment_gateway->payment_methods === self::PAYMENT_METHOD_IPAY88;

            $isIpay88Available = (new Ipay88Service())->isSupportedCurrency(CheckoutData::$currency->currency);

            $object = (object)
            [
                'id' => $payment_gateway->id,
                'name' => $payment_gateway->payment_methods,
                'displayName' => $payment_gateway->display_name,
                'description' => $payment_gateway->description,
                'api' => $payment_gateway->publishable_key,
                'enabled_at' => $isIpay88Payment ? $isIpay88Available : $payment_gateway->enabled_at,
                'enable_fpx' => $payment_gateway->enable_fpx,
                'min' => $isStripePayment ? 2 : 0.1,
            ];
            array_push($payment_methods, $object);

            $currencyDetails = $this->getDefaultCurrency();

            if (!$isStripePayment) continue;

            // add stripe FPX if there's stripe payment method and store currency is limited to MRY
            if ($payment_gateway->enable_fpx && ($currencyDetails->currency === 'MYR' || $currencyDetails->currency === 'RM')) {
                $object = (object)
                [
                    'id' => 'stripe-fpx',
                    'name' => self::PAYMENT_METHOD_STRIPE_FPX,
                    //'displayName' => $payment_gateway->display_name2,
                    'displayName' => 'Stripe FPX',
                    'api' => $payment_gateway->publishable_key,
                    'enabled_at' => $payment_gateway->enabled_at,
                    'min' => 2,
                ];
                array_push($payment_methods, $object);
            };
        }
        $payment_methods[] = $this->getStoreCreditPayment();
        $payment_methods[] = $this->getNonePayment();
        return $payment_methods;
    }

    public function getStripeSubscription()
    {
        $payment_gateway = PaymentAPI::firstWhere(['account_id' => $this->getCurrentAccountId(), 'payment_methods' => 'stripe']);
        $subscription = (object)
        [
            'id' => 'stripe-subscription',
            'name' => 'stripe subscription',
            'displayName' => 'stripe subscription',
            'api' => $payment_gateway->publishable_key,
            'enabled_at' => $payment_gateway->enabled_at,
            'min' => 0,
        ];
        return [$subscription];
    }

    public function getStoreCreditPayment()
    {
        return (object)
        [
            'id' => 'store-credit',
            'name' => self::PAYMENT_METHOD_STORE_CREDIT,
            'displayName' => 'Store Credit',
            'api' => null,
            'enabled_at' => 1,
            'min' => 0,
        ];
    }

    public function getNonePayment()
    {
        return (object)
        [
            'id' => 'none',
            'name' => self::PAYMENT_METHOD_NONE,
            'displayName' => 'None',
            'api' => null,
            'enabled_at' => 1,
            'min' => 0,
        ];
    }

    /**
     * Get detail of selected payment methods
     *
     * Noted: If grandTotal of order is 0, will return store credit payment methods
     *
     * @param  mixed $id
     * @return mixed
     */
    public function getSelectedPaymentMethod()
    {
        $paymentMethods = collect($this->getCheckoutPayment());
        return $paymentMethods->firstWhere('id', CheckoutData::$paymentMethodId);
    }

    public function getPaymentMethodByDisplayName($displayName)
    {
        return PaymentAPI::firstWhere('display_name', $displayName);
    }

    public function updateStripeAmount()
    {
        $selectedPaymentMethod = $this->getSelectedPaymentMethod();
        if (!str_contains($selectedPaymentMethod->name, 'stripe')) return;
        $type = $selectedPaymentMethod->name === self::PAYMENT_METHOD_STRIPE ? 'card' : 'fpx';
        (new StripeService)->updateStripePaymentIntent($type);
    }
}
