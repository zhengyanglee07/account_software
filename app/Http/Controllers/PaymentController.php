<?php

namespace App\Http\Controllers;

use Exception;
use App\Order;
use App\Currency;
use App\ShippingZone;
use App\PaymentAPI;
use App\ProcessedContact;
use App\UsersProduct;
use App\AccountDomain;
use App\Services\Checkout\CheckoutOrderService;
use Illuminate\Http\Request;
use App\Traits\AuthAccountTrait;
use Mail;
use App\Traits\CurrencyConversionTraits;
use Inertia\Inertia;
use App\Traits\PublishedPageTrait;
use App\Services\Checkout\Payment\StripeService;
use App\Services\Checkout\PaymentService;
use App\Services\RedisService;
use App\Traits\SalesChannelTrait;

class PaymentController extends Controller
{
    use CurrencyConversionTraits, AuthAccountTrait, PublishedPageTrait, SalesChannelTrait;

    /**
     * get curreny in an order
     *
     *@param $id integer
     */
    public function getOrderCurrency($id)
    {
        $order = Order::where('payment_references', $id)->first();
        $currencyDetails = Currency::where(['account_id' => $order->account_id, 'currency' => $order->currency])->first();
        $currencyDetails->currency = $order->currency;
        $currencyDetails->exchange_rate = $order->exchange_rate;
        return response()->json($currencyDetails);
    }

    /**
     * get rounding of a currency
     *
     *@param $id integer
     */
    public function getRoundingSettings(request $request)
    {
        $rounding = Currency::where('account_id', $request->accountId)
            ->where('currency', $request->selectedCurrency)
            ->first()
            ->rounding;
        return response()->json($rounding);
    }

    /**
     * get product
     *
     *@param $refkey string
     */
    public function getProduct($refKey)
    {
        $product = UsersProduct::where('reference_key', $refKey)->first();
        // $productOption = $this->getProductOption($product);
        // $productVariant = $this->getProductVariant($product);
        // $variantOptionArray = $productVariant['variantOptionArray'];
        // $variationCombination = $productVariant['variationCombination'];

        $selectedSaleChannels = [];
        foreach ($product->saleChannels()->get() as $saleChannel) {
            $selectedSaleChannels[] = $saleChannel->type;
        };
        if ($product->status === 'active' && count($product->saleChannels()->get()) === 0) $selectedSaleChannels = ['funnel', 'online-store', 'mini-store'];
        $product['selectedSaleChannels'] =  $selectedSaleChannels;

        $productCompacts = [
            'product' => $product,
            // 'productOption' => $productOption,
            // 'productVariant' => $productVariant,
            // 'variantOptionArray' => $variantOptionArray,
            // 'variationCombination' => $variationCombination,
        ];

        return response()->json($productCompacts);
    }

    /**
     * get product exists
     *
     *@param $refkey string
     */
    public function checkProductExists($refKey)
    {
        $product = usersProduct::where('reference_key', $refKey)->first();
        $isExists = $product !== null ? true : false;
        $isActive = true;
        if ($isExists) $isActive = $product->status === 'active' ? true : false;
        return response()->json($isExists && $isActive);
    }

    /**
     * get order customer detail
     *
     *@param $refkey string
     */
    public function getPreviousOrder($refKey)
    {
        $getOrder = Order::where('payment_references', $refKey)->first();
        $getProcessedContact = ProcessedContact::find($getOrder->processed_contact_id);
        $customerInfo = (object)[
            'fullName' => $getProcessedContact->fname,
            'email' => $getProcessedContact->email,
            'phoneNumber' => $getProcessedContact->phone_number,
        ];
        $shipping = (object)[
            'fullName' => $getOrder->shipping_name,
            'phoneNumber' => $getOrder->shipping_phoneNumber,
            'companyName' => $getOrder->shipping_comapany_name,
            'address' => $getOrder->shipping_address,
            'city' => $getOrder->shipping_city,
            'country' => $getOrder->shipping_country,
            'state' => $getOrder->shipping_state,
            'zipCode' => $getOrder->shipping_zipcode,
        ];
        $billing = (object)[
            'fullName' => $getOrder->billing_name,
            'phoneNumber' => $getOrder->billing_phoneNumber,
            'companyName' => $getOrder->billing_comapany_name,
            'address' => $getOrder->billing_address,
            'city' => $getOrder->billing_city,
            'country' => $getOrder->billing_country,
            'state' => $getOrder->billing_state,
            'zipCode' => $getOrder->billing_zipcode,
        ];
        $customerDetail = (object)[
            'customerInfo' => $customerInfo,
            'shipping' => $shipping,
            'billing' => $billing,
        ];
        return response()->json($customerDetail);
    }

    /**
     * check available region
     *
     *@param $request request
     */
    public function checkAvailableRegion(Request $request)
    {
        $account_id = $request->account_id;
        $customerCountry = $request->customerDetail['shipping']['country'];
        $customerState = $request->customerDetail['shipping']['state'];
        $customerZipcode = $request->customerDetail['shipping']['zipCode'];

        $shippingZoneAll = ShippingZone::where('account_id', $account_id)->get();
        $isAvailable = false;
        $method = [];
        $shippingFee = 0;

        $compareArr = [];
        foreach ($shippingZoneAll as $zone) {
            foreach ($zone->shippingRegion as $region) {
                if (!in_array($region->country, $compareArr)) {
                    array_push($compareArr, $region->country);
                }
                if ($region->country == $customerCountry) {
                    if ($region->region_type == 'zipcodes') {
                        if (in_array($customerZipcode, json_decode($region->zipcodes))) {
                            $isAvailable = true;
                            $method = $zone->shippingMethodDetails->where('shipping_method', 'Flat Rate')->first();
                            $shippingFee = ($method) ? floatval($method->per_order_rate) : 0;
                            break;
                        }
                    }
                }
            }
        }
        if (!$isAvailable) {
            foreach ($shippingZoneAll as $zone) {
                foreach ($zone->shippingRegion as $region) {
                    if ($region->country == $customerCountry) {
                        $states = array_map(function ($value) {
                            return $value['stateName'];
                        }, $region->state);
                        if (in_array($customerState, $states)) {
                            $isAvailable = true;
                            $method = $zone->shippingMethodDetails->where('shipping_method', 'Flat Rate')->first();
                            $shippingFee = ($method) ? floatval($method->per_order_rate) : 0;
                            break;
                        }
                    }
                }
            }
        }

        if (!$isAvailable & !in_array($customerCountry, $compareArr)) {
            foreach ($shippingZoneAll as $zone) {
                foreach ($zone->shippingRegion as $region) {
                    if ($region->country == 'Rest of world') {
                        $isAvailable = true;
                        $method = $zone->shippingMethodDetails->where('shipping_method', 'Flat Rate')->first();
                        $shippingFee = ($method) ? floatval($method->per_order_rate) : 0;
                        break;
                    }
                }
            }
        }
        return response()->json(['isRegionAvailable' => $isAvailable, 'shippingFee' => $shippingFee, 'method' => $method]);
    }

    /**
     * make payment in two step form and one step form
     * 
     * ! deprecated on 12/09/2022, use CheckoutController@checkoutOrder
     *
     *@param $request request
     */
    public function makeCheckout(request $request)
    {
        $secretAPI = paymentAPI::where('account_id', $request->accountId)
            ->where('payment_methods', 'Stripe')
            ->first()->secret_key;
        $customerDetail = $request->customerDetail;
        // set APi key
        \Stripe\Stripe::setApiKey($secretAPI);
        try {
            // retrieve JSON from POST body
            //create customer
            $customer = \Stripe\Customer::create([
                'name' => $customerDetail['fullName'],
                'email' => $customerDetail['email'],
                'phone' => $customerDetail['phoneNumber'],
            ]);
            //create payment intent
            $paymentIntent = \Stripe\PaymentIntent::create([
                'customer' => $customer->id,
                'amount' => $this->getCurrencyRange($request->productPrice, $request->currency),
                'currency' => $request->currency,
                'receipt_email' => $customerDetail['email'],
                'payment_method_types' => ['card'],
                'setup_future_usage' => 'off_session',
            ]);
            $data = [
                'clientSecret' => $paymentIntent->client_secret,
                'customerId' => $customer->id,
            ];
        } catch (Exception $e) {
            $data = ['error' => $e->getMessage()];
        }
        return response()->json($data);
    }

    /**
     * make one click upsell
     *
     *@param $request request
     */
    public function makeOneClickCheckout(Request $request)
    {
        $secretAPI = paymentAPI::where('account_id', $request->account_id)
            ->where('payment_methods', 'Stripe')
            ->first()->secret_key;
        $processContactId = Order::where('payment_references', $request->formId)->first()->processed_contact_id;
        $customerId = ProcessedContact::find($processContactId)->customer_id;
        // set APi key
        \Stripe\Stripe::setApiKey($secretAPI);
        try {
            //create payment methods
            $payment_methods = \Stripe\PaymentMethod::all([
                'customer' => $customerId,
                'type' => 'card'
            ]);
            //create payment intent
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $this->getCurrencyRange($request->productPrice * 100, $request->currency),
                'currency' => $request->currency,
                'customer' => $customerId,
                'payment_method' => $payment_methods->data[0]->id,
                'off_session' => true,
                'confirm' => true
            ]);
            return response()->json(['paymentIntent' => $paymentIntent, 'customerId' => $customerId,]);
        } catch (Error $e) {
            http_response_code(500);
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    /**
     * get customer detail through contact random id
     *
     *@param $request request
     */
    public function getLocalStorage(request $request)
    {
        $processContact = ProcessedContact::where('contactRandomId', $request->contactRandomId)->first();
        $orders = Order::where('account_id', $processContact->account_id)
            ->where('processed_contact_id', $processContact->id)
            ->orderBY('created_at', 'DESC')
            ->first();
        $obj = array(
            'order' => $orders,
            'processContact' => $processContact,
        );
        return response()->json($obj);
    }

    /**
     * get order summary
     *
     *@param $request request
     */
    public function getOrderSummery(request $request)
    {
        $combineArray = [];
        $combineOrder = [];
        foreach ($request->orid as $id) {
            $order = Order::where('payment_references', $id)->first();
            if ($order) {
                $order->order_details = $order->orderDetails;
                $order->currency = ($order->currency == 'MYR') ? 'RM' : $order->currency;
                array_push($combineOrder, $order);
            }
        }
        return response()->json([
            'orderSummery' => $combineOrder,
            'order' => $combineArray,
        ]);
    }


    //************************ stripe payment ********************************
    public function retrieveStripePaymentAPI()
    {
        $stripePaymentAPI = PaymentAPI
            ::where('account_id', $this->getCurrentAccountId())
            ->firstWhere('payment_methods', 'Stripe');

        return response()->json($stripePaymentAPI);
    }
    //************************ stripe payment ********************************

    //************************ stripe Fpx payment ********************************
    public function createFPXPaymentIntent(request $request)
    {
        $secretAPI = PaymentAPI
            ::where('account_id', $request->accountId)
            ->where('payment_methods', 'Stripe')
            ->first()
            ->secret_key;
        \Stripe\Stripe::setApiKey($secretAPI);
        $paymentIntentId = $request->paymentIntentId;

        if (!$paymentIntentId) {
            $payment_intent = \Stripe\PaymentIntent::create(
                [
                    'payment_method_types' => ['fpx'],
                    'amount' => $request->totalAmount,
                    'currency' => $request->currency,
                ]
            );
        } else {
            $payment_intent = \Stripe\PaymentIntent::update(
                $paymentIntentId,
                ['amount' => $request->totalAmount]
            );
        }

        return response()->json([
            'payment_intent_id' => $payment_intent['id'],
            'secret' => $payment_intent['client_secret']
        ]);
    }
    //************************ stripe Fpx payment ********************************

    //************************ senangPay payment ********************************

    public function getHash(request $request)
    {
        $verifyKey = PaymentAPI::where('account_id', $request->accountId)->where('payment_methods', 'senangPay')->first()->secret_key;
        $hash = hash_hmac('sha256', $verifyKey . urldecode($request->detail) . urldecode($request->amount) . urldecode($request->orderId), $verifyKey);
        return response()->json($hash);
    }

    public function getPaymentResponse(request $request)
    {
        $verifyKey = PaymentAPI::where('account_id', $request->order_id)->where('payment_methods', 'senangPay')->first()->secret_key;
        $hash = hash_hmac('sha256', $verifyKey . urldecode($request->status_id) . urldecode($request->order_id) . urldecode($request->transaction_id) . urldecode($request->msg), $verifyKey);
        $order = Order::where('payment_references', $request->payment_references)->first();
        $payment = PaymentAPI::firstWhere([
            'account_id' => $order->account_id,
            'payment_methods' => 'senangPay'
        ]);
        $url = $request->url;
        $type = AccountDomain::getDomainRecord()->type;

        if ($hash !== $request->hash) {
            $status = 'Failed';
            setcookie("status", 'fail', 0, "/");
        } else {
            if ($request->status_id === '1') {
                $status = 'Success';

                $params =  null;
                $cashback = json_decode($request->cashback, true);
                $appliedPromotion = json_decode($request->appliedPromotion, true);
                if (isset($cashback) && isset($request->storeCreditTotal) && isset($request->usedCredit)) {
                    $params['cashback'] = $cashback;
                    $params['storeCreditTotal'] = $request->storeCreditTotal;
                    $params['usedCredit'] = $request->usedCredit;
                }
                $redirectUrl = app('App\Http\Controllers\OnlineStoreCheckoutController')->paymentSuccess($order, $params, $appliedPromotion);
            } else if ($request->status_id === '0') {
                $status = 'Failed';
                $redirectUrl = $type === 'mini-store' ? '/checkout/mini-store' : '/checkout/payment';
                setcookie("status", 'fail', 0, "/");
            }
        }
        $order->payment_process_status = $status;
        $order->payment_method = $payment->display_name ?? 'senangPay';
        $order->save();
        return response()->json([
            'status' => $status,
            'message' => $request->msg,
            'redirectUrl' => $redirectUrl
        ]);
    }
    //************************ senangPay payment ********************************


    /*===============================================================================================================*/
    /*                                         Currently in use started on 10/08/2022                                */
    /*===============================================================================================================*/
    public $stripeService;

    public function __construct()
    {
        $this->stripeService = new StripeService();
    }


    public function createStripePaymentIntent()
    {
        return response($this->stripeService->createStripePaymentIntent());
    }

    public function createStripeFPXPaymentIntent()
    {
        return response($this->stripeService->createStripePaymentIntent('fpx'));
    }

    public function senangpayRedirect(Request $request)
    {
        $publishPageBaseData = $this->getPublishedPageBaseData();
        $returnUrl = $this->checkIsCurrentSalesChannel('mini-store') ? '/checkout/mini-store' : '/checkout/payment';

        $isPaymentSuccess = $request->status_id === '1';

        if (!$isPaymentSuccess)
            RedisService::set('paymentError', 'Failed SenangPay payment: ' . str_replace('_', ' ', $request->msg));

        return Inertia::render('online-store/pages/' . 'PaymentRedirection', array_merge($publishPageBaseData, [
            'isPaymentSuccess' => (bool)$isPaymentSuccess,
            'returnUrl' => $returnUrl,
        ]));
    }

    public function ipay88Response(Request $request)
    {
        $publishPageBaseData = $this->getPublishedPageBaseData();
        $returnUrl = $this->checkIsCurrentSalesChannel('mini-store') ? '/checkout/mini-store' : '/checkout/payment';

        switch ($request->Status) {
            case '1':
                $paymentProcessStatus = PaymentService::PAYMENT_PROCESS_SUCCESS_STATUS;
                break;
            case '0':
                $paymentProcessStatus = PaymentService::PAYMENT_PROCESS_FAILED_STATUS;
                break;
            case '6':
                $paymentProcessStatus = PaymentService::PAYMENT_PROCESS_PENDING_STATUS;
                break;
            default:
                $paymentProcessStatus = PaymentService::PAYMENT_PROCESS_PENDING_STATUS;
                break;
        }

        Order::firstWhere('reference_key', $request->RefNo)->update(['payment_process_status' => $paymentProcessStatus]);

        // Payment status 6 = Pending payment for not real-time payment (exp: pay4ME, CIMB Virtual Account)
        $isPaymentSuccess = $request->Status === '1' || $request->Status === '6';

        if (!$isPaymentSuccess)
            RedisService::set('paymentError', 'Failed Ipay88 payment: ' . str_replace('_', ' ', $request->ErrDesc));

        return Inertia::render('online-store/pages/' . 'PaymentRedirection', array_merge($publishPageBaseData, [
            'isPaymentSuccess' => (bool)$isPaymentSuccess,
            'returnUrl' => $returnUrl,
        ]));
    }
    public function ipay88Callback(Request $request)
    {
        $order = Order::firstWhere('reference_key', $request->RefNo);

        if($order->payment_process_status === PaymentService::PAYMENT_PROCESS_SUCCESS_STATUS) return;

        // Payment successfully processed
        if($request->Status === '1'){
            (new CheckoutOrderService)->updateOrderPaymentStatus($order);
        }
    }
}
