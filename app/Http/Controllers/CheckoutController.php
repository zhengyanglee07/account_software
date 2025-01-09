<?php

namespace App\Http\Controllers;

use App\Currency;
use App\Events\OrderPlaced;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutFormRequest;
use App\Page;
use App\Order;
use App\Repository\Checkout\CheckoutData;
use App\Repository\Checkout\FormDetail;
use App\Repository\CheckoutRepository;
use App\Services\Checkout\CartService;
use App\Services\CheckoutService;
use App\Services\RedisService;
use App\Services\Checkout\CashbackService;
use App\Services\Checkout\CheckoutErrorService;
use App\Services\Checkout\CheckoutOrderService;
use App\Services\Checkout\FunnelCheckoutService;
use App\Services\Checkout\OrderFulfillmentService;
use App\Services\Checkout\OutOfStockService;
use App\Services\Checkout\Payment\Ipay88Service;
use App\Services\Checkout\Payment\SenangPayService;
use App\Services\Checkout\Payment\StripeService;
use App\Services\Checkout\PaymentService;
use App\Services\Checkout\PromotionService;
use App\Services\Checkout\ShippingService;
use App\Services\Checkout\StoreCreditService;
use App\Services\ProcessedContactService;
use App\Traits\PublishedPageTrait;
use App\Traits\SalesChannelTrait;
use App\UsersProduct;
use Auth;
use Carbon\Carbon;
use Inertia\Inertia;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    use PublishedPageTrait, SalesChannelTrait;

    public const IS_DELETE_SHIPPING_OPTION_IF_NOT_REQUIRED = true;
    public const IS_DELETE_REMARK_IF_EMPTY = true;
    public const IS_DELETE_SHIPPING_METHOD_IF_NOT_REQUIRED = true;

    public function getShoppingCartData()
    {
        return response()->json([
            ...$this->getPublishedPageBaseData(),
            'cartItems' => (new CartService)->getCartItems(),
        ]);
    }

    public function getCheckoutData(Request $request)
    {
        $checkoutServices = new CheckoutService($request->path);
        $publishPageBaseData = $this->getPublishedPageBaseData();
        $data = array_merge($publishPageBaseData, ['storeData' => $checkoutServices->getStoreData($request)]);
        return response()->json($data);
    }

    public function showCheckoutPages(Request $request)
    {
        $checkoutServices = new CheckoutService($request->path);
        $publishPageBaseData = $this->getPublishedPageBaseData();
        return Inertia::render(
            $checkoutServices->getCheckoutPagePath(),
            array_merge($publishPageBaseData, ['storeData' => $checkoutServices->getStoreData($request)])
        );
    }

    public function checkoutDeliveryArea(Request $request)
    {
        RedisService::set('shippingOption', $request->delivery, self::IS_DELETE_SHIPPING_OPTION_IF_NOT_REQUIRED);
        RedisService::set('shippingAddress', $request->shipping);

        $hasValidShipping = true;
        if (empty($request->delivery['type']) || $request->delivery['type'] === 'delivery') {
            $hasValidShipping = (new ShippingService)->checkShippingMethodAvailability();
            if (!$hasValidShipping) {
                RedisService::delete('shippingOption');
                RedisService::delete('shippingAddress');
            }
        }
        return response()->json(['isValid' => $hasValidShipping]);
    }

    public function checkoutCart(Request $request)
    {
        $cartItems = array_map(function ($item) {
            $item['checkoutAt'] = (string)Carbon::now();
            return $item;
        }, $request->cartItems);
        RedisService::set('cartItems', $cartItems);
        $checkoutRepository = new CheckoutRepository();
        return response()->json($checkoutRepository::$cartItems);
    }

    public function checkoutOutOfStock(Request $request)
    {
        $cartItems  = (new CheckoutData)::$cartItems;
        $outOfStockProductRefs = explode(',', $request->outOfStockProducts);
        $availableCartItem = $cartItems->whereNotIn('reference_key', $outOfStockProductRefs)->toArray();
        RedisService::set('cartItems', $availableCartItem);
    }

    public function checkoutShippingOption(Request $request)
    {
        RedisService::set('shippingOption', $request->delivery, self::IS_DELETE_SHIPPING_OPTION_IF_NOT_REQUIRED);
    }

    public function checkoutForm(CheckoutFormRequest $request)
    {
        if (
            !empty($request->customerInfo['password']) &&
            !Auth::guard('ecommerceUsers')->check()
        ) {
            $isAccountExists = app(API\EcommerceAuthController::class)->isCustomerAccountExists($request->customerInfo['email']);

            $request->merge([...$request->customerInfo, 'isSkipEmail' => true]);
            $response = app(API\EcommerceAuthController::class)->{$isAccountExists ? 'login' : 'register'}($request);
            $responseContent = $response->getOriginalContent() ?? null;
            $token = $responseContent['token'];
        }

        RedisService::set('formDetail', [
            'customerInfo' => $request->customerInfo,
            'shipping' => $request->shipping,
            'billing' => $request->billing,
        ]);

        if (!$this->checkIsCurrentSalesChannel('mini-store'))
            RedisService::set('shippingOption', $request->delivery, self::IS_DELETE_SHIPPING_OPTION_IF_NOT_REQUIRED);
        else RedisService::delete('shippingMethod');

        $checkoutRepository = new CheckoutRepository();
        return response()->json([
            'manualShippingMethods' => (new ShippingService)->getManualShipping(),
            'taxSettings' => $checkoutRepository::$taxSettngs,
            'cashback' => $checkoutRepository::$cashback,
            'storeCredit' =>  $checkoutRepository::$storeCredit,
            'token' => $token ?? '',
        ]);
    }

    public function checkoutShipping(Request $request)
    {
        RedisService::set('shippingMethod', $request->shipping, self::IS_DELETE_SHIPPING_METHOD_IF_NOT_REQUIRED);
        CheckoutData::setSelectedShippingMethodDetail();
        $shippingService = new ShippingService();
        RedisService::append('shippingMethod', 'charge', $shippingService->getShippingCharge());
        $checkoutRepository = new CheckoutRepository();

        return response()->json([
            'cashback' => $checkoutRepository::$cashback,
            'storeCredit' => $checkoutRepository::$storeCredit,
        ]);
    }

    public function checkoutTwoStepForm(Request $request)
    {
        RedisService::deleteAll();

        $funnelService = new FunnelCheckoutService();

        $funnelService->setFormDetail($request);
        $userProduct = $funnelService->setCartItems($request->product);

        CheckoutRepository::setCartItem();

        // Form validation
        app(CheckoutFormRequest::class);

        $shipping = $funnelService->setShippingMethod($userProduct);
        $payment = $funnelService->setPaymentMethod();

        // Referral action for new signup user
        $processedContact = (new ProcessedContactService)->updateOrCreateProcessedContact(new FormDetail, ['acquisition_channel' => 'funnel']);
        $newSignUp = $this->newSignUp($processedContact,  $request->funnelId);
        $user = $this->referralUser($processedContact, $request->funnelId);
        if ($request->hasCookie('referral') && $newSignUp) {
            $this->checkReferralCampaignAction($request->getHost(), 'sign-up', $request->funnelId, $processedContact);
        }
        RedisService::set('processedContactId', $processedContact->id);

        new CheckoutRepository();

        return response()->json([
            'hasPhysicalProduct' => CheckoutRepository::$hasPhysicalProduct,
            'formDetail' => RedisService::get('formDetail'),
            'isProductCourseEnrolled' => $userProduct->isEnrolled,
            'paymentError' => $payment['paymentError'],
            'isRegionAvailable' => $shipping['isRegionAvailable'],
            'manualShipping' => $shipping['manualShipping'],
            'selectedShipping' => CheckoutData::$shippingMethod,
            'selectedProduct' => CheckoutRepository::$cartItems,
            'taxSettings' => CheckoutRepository::$taxSettngs,
            'isOutOfStock' => count((new OutOfStockService())->getOutOfStockProduct()) > 0,
            'user' => $user,
            'promotion' => CheckoutRepository::$availablePromotions,
            'hasActivePromotion' => (new PromotionService)->hasActivePromotion(false),
        ]);
    }

    public function checkoutCartItemQuantity()
    {
        RedisService::append('cartItems', 'qty', (int)request()->quantity, 0);
        new CheckoutRepository();
        return response()->json([
            'cartItems' => CheckoutRepository::$cartItems,
            'promotion' => CheckoutRepository::$availablePromotions,
            'hasActivePromotion' => (new PromotionService)->hasActivePromotion(false),
        ]);
    }

    public function checkoutOneClickUpsell(Request $request)
    {
        RedisService::deleteAll(['formDetail']);
        if (!RedisService::get('formDetail')) {
            abort(500, 'Sorry you cannot purchase this product.');
        }

        $funnelService = new FunnelCheckoutService();

        try {
            $funnelService->setCartItems($request->product);
            $funnelService->setPaymentMethod();
        } catch (\Throwable $th) {
            throw new \RuntimeException($th->getMessage());
        }
    }

    public function checkoutOrder(Request $request)
    {
        $isFunnel = CheckoutRepository::$isFunnel;

        $isOneClickUpsell = $request->type === 'one-click-upsell';
        if ($isOneClickUpsell) {
            $this->checkoutOneClickUpsell($request);
        }

        if ($isFunnel) {
            RedisService::append('cartItems', 'qty', (int)$request->qty, 0);
            new CheckoutRepository();
        } else if (
            empty($request->paymentMethod) ||
            ($request->paymentMethod === 'store-credit' && CheckoutRepository::$grandTotal > 0) ||
            ($request->paymentMethod === 'none' && CheckoutRepository::$grandTotal === 0)
        ) {
            abort(500, 'Please select payment method to proceed');
        }

        $outOfStockProduct = (new OutOfStockService())->getOutOfStockProduct();
        if (count($outOfStockProduct)) {
            $firstProduct = array_values($outOfStockProduct)[0];
            abort(422, 'Sorry, product is out of stock');
            // abort(422, 'Only ' . $firstProduct->quantity . ' is available for ' . $firstProduct->productTitle . '. Please update your quantity');
        };

        RedisService::set('remark', $request->remark, self::IS_DELETE_REMARK_IF_EMPTY);
        RedisService::set('paymentMethod', $request->paymentMethod, true, true);

        $orderService = new CheckoutOrderService();

        try {
            \DB::beginTransaction();

            if ($isFunnel) {
                $orderService->setGrandTotal();
                $orderService->setFunnelCheckout($request->landingId, (object)[
                    'fId' => $request->fId,
                    'visitorId' => $request->visitorId,
                    'cartId' => $request->cartId,
                ]);
            }

            if ($isOneClickUpsell) (new StripeService)->createPaymentIntentByPaymentId($request->fId);
            else (new PaymentService)->updateStripeAmount();

            $processedContact = $orderService->updateOrCreateProcessedContact();
            RedisService::set('processedContactId', $processedContact->id);

            $order = $orderService->createOrder($processedContact);
            $orderDetails = $orderService->createOrderDetail($order);
            $orderService->createOrderTransaction($order);

            if (isset($order->shipping_method) && RedisService::get('shippingMethod')) {
                CheckoutData::setSelectedShippingMethodDetail();
                optional(OrderFulfillmentService::fulfillService())->create($order, $orderDetails);
            }

            $data = $orderService->handleReferralAction($request, $order, $isOneClickUpsell);

            RedisService::set('paymentReferences', $order->payment_references);

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            Log::error(array_slice($th->getTrace(), 0, 10, true));
            Log::error($th->getMessage());
            throw new \RuntimeException('Failed to place order. Please contact support');
        }

        if ($orderService->isProcessOrderInstantly() || $isOneClickUpsell) {
            return $this->checkoutOrderProcess();
        }

        $senangPayService = new SenangPayService($order);
        $ipay88Service = new Ipay88Service($order);

        $response = response()->json([
            'paymentMethod' => [
                'name' => $orderService->paymentMethod->name,
                'senangPay' => $senangPayService->getParameters(),
                'ipay88' => $ipay88Service->getPaymentRequestParameter(),
                'paymentReferences' => $order->payment_references,
            ],
            'order' => $order,
            'grandTotal' => $orderService->grandTotal,
            'contactRandomId' => $processedContact->contactRandomId,
        ]);

        if (isset($data['cookie'])) $response->withCookie($data['cookie']);

        return $response->withCookie(cookie('clientId', session()->getId(), RedisService::REDIS_EXPIRED_TIME_IN_SECONDS / 60));
    }

    /**
     * Complete checkout order proccess after payment successful
     *
     * @return response
     */
    public function checkoutOrderProcess()
    {
        $returnUrl = $this->checkIsCurrentSalesChannel('mini-store') ? '/checkout/mini-store' : '/checkout/payment';

        $paymentRef = RedisService::get('paymentReferences');
        try {
            \DB::beginTransaction();

            $orderService = new CheckoutOrderService();
            $order = $orderService->getOrderByPaymentRef($paymentRef);

            $isFunnel = !empty($order->funnel_id);
            CheckoutRepository::setIsFunnel($isFunnel);

            $orderService->updateOrderPaymentStatus($order);
            /*
           *    Must update store credits first before saving cashback amount
            *   as customer not able to use the cashback earned directly from same order
            *  Process:
            *       1 - Record store credit usage
            *       2 - Record cashback earned
            *       3 - Create Order Discount
            *       3 - Update Product Inventory
            */
            // Skip store credit, cashback and discount for funnel
            if (!$isFunnel) {
                (new StoreCreditService)->recordStoreCreditUsage($order);

                if ($orderService->paymentMethod->name !== PaymentService::PAYMENT_METHOD_MANUAL) {
                    (new CashbackService)->recordCashbackEarned($order);
                }
            }
            $orderService->createOrderDiscount($order);

            $orderService->updateProductInventory($order);

            $orderService->funnelCheckoutTrack($order);

            \DB::commit();

            $order->refresh();

            event(new OrderPlaced($order));
            $orderService->sendOrderNotification($order);
        } catch (\Throwable $th) {
            \DB::rollBack();
            Log::error(array_slice($th->getTrace(), 0, 10, true));
            Log::error($th->getMessage());
            CheckoutErrorService::setError('Failed to process order. Please contact support');
            return abort(302, $returnUrl);
        }

        // Remove data from redis after payment sucesfully
        $dataToDelete = [
            'lastAccessedAt', 'cartItems', 'promotion', 'remark',
            'paymentMethod', 'paymentReferences', 'paymentError',
            'stripeClientSecret', 'stripeFPXClientSecret', 'shippingMethod',
            'processedContactId',
        ];
        foreach ($dataToDelete as $key) {
            RedisService::delete($key);
        }

        $redirectUrl = '/checkout/success/' . $order->payment_references;

        return response()->json(['redirectUrl' => $redirectUrl, 'paymentReferences' => $paymentRef]);
    }

    public function getTwoStepFormData()
    {
        $accountId  = $this->getCurrentAccountId();
        $data = (new CheckoutService())->getTwoStepFormData();
        $data['products']
            = UsersProduct::with(['variant_details', 'variant_values'])->where([
                'account_id' => $accountId,
                'status' => 'active',
            ])->latest()->get();
        $this->setVariantLabel($data['products']);
        $data['landingPage'] = Page::ofPublished()->ofPath($accountId, request()->query('path'))->first();
        return response()->json($data);
    }

    public function getOrderData($paymentRef)
    {
        $order = Order::firstWhere('payment_references', $paymentRef);
        return response()->json([
            'order' => $order,
            'currencyDetail' =>  Currency::firstWhere(['account_id' => $this->getCurrentAccountId(), 'isDefault' => 1]),
        ]);
    }
}
