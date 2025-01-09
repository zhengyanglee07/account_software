<?php

namespace App\Services\Checkout\Payment;

use App\Order;
use App\PaymentAPI;
use App\ProcessedContact;

use App\Repository\CheckoutRepository;
use App\Repository\Checkout\CheckoutData;

use App\Services\RedisService;

use App\Traits\AuthAccountTrait;
use App\Traits\Checkout\CheckoutCurrencyTrait;
use App\Traits\Checkout\CheckoutOrderTrait;

class StripeService
{
    use AuthAccountTrait, CheckoutCurrencyTrait, CheckoutOrderTrait;

    protected $accountId, $stripePaymentAPI;

    public function __construct()
    {
        $this->accountId = $this->getCurrentAccountId();
        $this->stripePaymentAPI = PaymentAPI
            ::where('account_id', $this->accountId)
            ->firstWhere('payment_methods', 'Stripe');

        if ($this->stripePaymentAPI)
            \Stripe\Stripe::setApiKey($this->stripePaymentAPI->secret_key);
    }

    public function createStripePaymentIntent($paymentMethodType = 'card', $isRetry = true)
    {
        $currency = CheckoutData::$currency->currency;
        $customerInfo = CheckoutData::$formDetail->customerInfo;

        $redisKeyMap = [
            'card' => 'stripeClientSecret',
            'fpx' => 'stripeFPXClientSecret',
        ];

        try {
            $clientSecret = RedisService::get($redisKeyMap[$paymentMethodType]);

            if (empty($clientSecret)) {
                $customer = $this->updateOrCreateStripeCustomer($customerInfo);

                // Create a PaymentIntent with amount and currency
                $params = [
                    'amount' => $this->getConversionPriceForStripe(CheckoutRepository::$grandTotal),
                    'currency' => strtolower($currency),
                    'payment_method_types' => [$paymentMethodType],
                    'customer' => $customer->id,
                    'receipt_email' => $customerInfo->email,
                ];
                if ($paymentMethodType === 'card') $params['setup_future_usage'] = 'off_session';

                $paymentIntent = \Stripe\PaymentIntent::create($params);
                $clientSecret = $paymentIntent->client_secret;
                RedisService::set($redisKeyMap[$paymentMethodType], $clientSecret);
            }

            $this->stripePaymentAPI->clientSecret = $clientSecret;
            return $this->stripePaymentAPI;
        } catch (Error $e) {
            if ($isRetry) $this->createStripePaymentIntent($paymentMethodType, false);
            abort(500, $e->getMessage());
        }
    }

    public function createPaymentIntentByPaymentId($paymentRef)
    {
        try {
            $currency = CheckoutData::$currency->currency;

            $order = Order::where('payment_references', $paymentRef)->first();
            $customerId = $order->processedContact()->first()->customer_id;
            
            $payment_methods = \Stripe\PaymentMethod::all([
                'customer' => $customerId,
                'type' => 'card'
            ]);

            $params = [
                'amount' => $this->getConversionPriceForStripe(CheckoutRepository::$grandTotal),
                'currency' => strtolower($currency),
                'customer' => $customerId,
                'payment_method' => $payment_methods->data[0]->id,
                'off_session' => true,
                'confirm' => true
            ];

            $paymentIntent = \Stripe\PaymentIntent::create($params);
            RedisService::set('stripeClientSecret', $paymentIntent->client_secret);
        } catch (Error $e) {
            abort(500, $e->getMessage());
        }
    }

    public function updateStripePaymentIntent($paymentMethodType = 'card')
    {
        $redisKeyMap = [
            'card' => 'stripeClientSecret',
            'fpx' => 'stripeFPXClientSecret',
        ];
        $clientSecret = RedisService::get($redisKeyMap[$paymentMethodType]);
        $explodedClientSecret = explode('_secret_', $clientSecret);
        \Stripe\PaymentIntent::update(
            $explodedClientSecret[0],
            ['amount' => $this->getConversionPriceForStripe(CheckoutRepository::$grandTotal)]
        );
    }


    public function updateOrCreateStripeCustomer($customerInfo)
    {
        $processedContactId = RedisService::get('processedContactId');

        $processedContact = ProcessedContact::find($processedContactId);
        $isCreateNewCustomer = empty(optional($processedContact)->customer_id);
        if (!$isCreateNewCustomer) {
            try {
                $customer = \Stripe\Customer::update(
                    $processedContact->customer_id,
                    [
                        'name' => $customerInfo->fullName,
                        'phone' => $customerInfo->phoneNumber,
                    ]
                );
            } catch (\Throwable $th) {
                if (str_contains($th->getMessage(), 'No such customer'))
                    $isCreateNewCustomer = true;
            }
        }

        if ($isCreateNewCustomer) {
            $customer = \Stripe\Customer::create([
                'name' => $customerInfo->fullName,
                'email' => $customerInfo->email,
                'phone' => $customerInfo->phoneNumber,
            ]);
            optional($processedContact)->update(['customer_id' => $customer->id]);
        }
        return $customer;
    }
}
