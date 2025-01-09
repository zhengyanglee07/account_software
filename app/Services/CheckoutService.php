<?php

namespace App\Services;

use App\Account;
use App\Delyva;
use App\EasyParcel;
use App\EcommercePreferences;
use App\Lalamove;
use App\Location;
use App\MiniStore;
use App\Models\StoreTheme;
use App\Order;
use App\Repository\Checkout\CheckoutData;
use App\Repository\CheckoutRepository;
use App\User;

use App\Traits\LegalPolicyTrait;
use App\Traits\SalesChannelTrait;
use App\Services\Checkout\PaymentService;
use App\Traits\Checkout\CheckoutOrderTrait;

use App\Services\Checkout\CartService;
use App\Services\Checkout\CashbackService;
use App\Services\Checkout\CustomerAccountService;
use App\Services\Checkout\FormServices;
use App\Services\Checkout\PromotionService;
use App\Services\Checkout\ShippingService;
use App\Services\Checkout\CheckoutErrorService;
use App\Services\Checkout\OutOfStockService;

use Illuminate\Http\Request;

class CheckoutService
{
    use LegalPolicyTrait, CheckoutOrderTrait, SalesChannelTrait;

    public const CHECKOUT_PROCESSING_PATH = '/checkout/processing';

    protected $checkoutPageMap = [
        'mini-store' => 'MiniStoreCheckoutPage',
        'information' => 'CustomerInformation',
        'shipping' => 'ShippingMethod',
        'payment' => 'PaymentMethod',
        'processing' => 'OrderProcessingPage',
        'success' => 'CheckoutSuccessful',
        'outOfStock' => 'OutOfStock',
    ];

    protected $path;
    public $customerAccountService, $formService, $paymentService;

    public function __construct(string $path = null)
    {
        new CheckoutData();
        $this->path = $path;

        $this->customerAccountService = new CustomerAccountService();
        $this->formService = new FormServices();
        $this->paymentService = new PaymentService();
    }


    /**
     * Get relative path of checkout page
     *
     * @return string
     */
    public function getCheckoutPagePath(): string
    {
        if (!isset($this->checkoutPageMap[$this->path])) abort(404);
        return ($this->path === 'mini-store' ? 'mini-store' : 'online-store') . '/pages/' . $this->checkoutPageMap[$this->path];
    }

    public function getStoreData(Request $request): array
    {
        if ($this->path === 'mini-store') {
            RedisService::delete('shippingMethod');
            new CheckoutData();
        }
        if ($this->path === 'processing') {
            return $this->getCheckoutProcessingData();
        }
        if ($this->path === 'success') {
            return $this->getCheckoutSuccessData($request->refKey);
        }
        return array_merge($this->getBaseStoreData(), $this->getExtraStoreDataBasedOnPage($this->path));
    }

    /**
     * Get data required for all checkout pages
     *
     * @return array
     */
    public function getBaseStoreData(): array
    {
        $accountId = $this->getCurrentAccountId();
        $account = Account::find($accountId, 'company');
        $user = User::where('currentAccountId', $accountId)->first('email');
        $preferences = EcommercePreferences::firstWhere('account_id', $accountId);
        $legalPolicy = $this->getAvailableLegalPolicy();
        $themeStyles = StoreTheme::first();

        $paymentError = RedisService::get('paymentError');
        RedisService::delete('paymentError');

        return [
            'account' => $account ?? null,
            'user' => $user ?? null,
            'preferences' => $preferences ?? null,
            'legalPolicy' => $legalPolicy ?? [],
            'location' => Location::first() ?? null,
            'taxSetting' => CheckoutRepository::$taxSettngs ?? null,
            'formDetail' => CheckoutData::$formDetail,
            'shippingOption' => CheckoutData::$shippingOption,
            'remark' => CheckoutData::$remark,
            'promotion' =>  CheckoutRepository::$availablePromotions ?? [],
            'cartItems' => CheckoutRepository::$cartItemsWithPromotion ?? [],
            'salesChannel' => $this->getCurrentSalesChannel(),
            'isShippingRequired' => $this->formService->isShippingRequired(),
            'customerAccount' => $this->customerAccountService->getCustomerAccountDetail(),
            'checkoutError' => CheckoutErrorService::getError(),
            'paymentError' => $paymentError,
            'hasActivePromotion' => (new PromotionService)->hasActivePromotion(),
            'allCashback' => (new CashbackService)->getCashback(),
            'cashback' => CheckoutRepository::$cashback,
            'storeCredit' => CheckoutRepository::$storeCredit,
            'hasSelectedShipping' => count((array)CheckoutData::$shippingMethod),
            'themeStyles' => $themeStyles ?? null,
        ];
    }

    /**
     * Get required data for each checkout pages
     *
     * @param  mixed $page
     * @return array
     */
    public function getExtraStoreDataBasedOnPage($page): array
    {
        $accountId = $this->getCurrentAccountId();
        $extraData = [];

        // Customer Information
        if ($page === 'mini-store' || $page === 'information') {
            $extraData['customOrders'] = $this->getCustomOrders('delivery_date');
        }

        // Shipping
        if ($page === 'mini-store' || $page === 'shipping') {
            $extraData['manualShippingMethods'] = (new ShippingService)->getManualShipping();
            $extraData['hasEasyParcel'] = EasyParcel::where('account_id', $accountId)->where('easyparcel_selected', 1)->exists();
            $extraData['hasLalamove'] = Lalamove::where('account_id', $accountId)->where('lalamove_selected', 1)->exists();
            $extraData['hasDelyva'] =  Delyva::where('account_id', $accountId)->where('delyva_selected', 1)->exists();
            $extraData['selectedShippingMethod'] = CheckoutData::$shippingMethod;
        }

        // Payment
        if ($page === 'mini-store' || $page === 'payment') {
            $extraData['selectedShippingMethod'] = CheckoutData::$shippingMethod;

            $extraData['paymentMethods'] = $this->paymentService->getCheckoutPayment();
            $extraData['selectedPaymentMethod'] = $this->paymentService->getSelectedPaymentMethod();
        }

        // Out of stock
        if ($page === 'mini-store' || $page === 'outOfStock') {
            $extraData['outOfStockProduct'] = (new OutOfStockService)->getOutOfStockProduct();
        }

        return $extraData;
    }

    public function getCheckoutProcessingData()
    {
        $returnUrl = $this->checkIsCurrentSalesChannel('mini-store') ? '/checkout/mini-store' : '/checkout/payment';
        return compact('returnUrl');
    }

    public function getCheckoutSuccessData($paymentRef)
    {
        $accountId = $this->getCurrentAccountId();
        $order = Order::firstWhere('payment_references', $paymentRef);
        $account = Account::find($accountId, 'company');
        $cartItems = CartService::getCartItemsFromOrder($order);
        $user = User::where('currentAccountId', $accountId)->first('email');
        $paymentMethod = $this->paymentService->getPaymentMethodByDisplayName($order->payment_method);
        return [
            'salesChannel' => $this->getCurrentSalesChannel(),
            'account' => $account,
            'user' => $user,
            'cartItems' => $cartItems,
            'formDetail' => $this->formService->getFormDetailFromOrder($order),
            'location' => Location::first(),
            'order' => $order,
            'selectedPaymentMethod' => $paymentMethod,
            'isShippingRequired' => $order->shipping,
            'customerAccount' => $this->customerAccountService->getCustomerAccountDetail(),
        ];
    }

    public function getDeliveryAreaCheckerData()
    {
        $accountId = $this->getCurrentAccountId();
        $customOrders = $this->getCustomOrders('delivery_date');
        $user = User::where('currentAccountId', $accountId)->first('email');
        $location = Location::first();
        $preferences = EcommercePreferences::firstWhere('account_id', $accountId);
        $miniStoreDetails = MiniStore::where('account_id', $accountId)->first();
        $shippingOption = RedisService::get('shippingOption');
        $shippingAddress = RedisService::get('shippingAddress');
        return compact(
            'user',
            'preferences',
            'location',
            'miniStoreDetails',
            'customOrders',
            'shippingOption',
            'shippingAddress'
        );
    }

    public function getTwoStepFormData()
    {
        $formDetail = (new CheckoutData)::$formDetail;
        $salesChannel = 'funnel';
        return compact(
            'formDetail',
            'salesChannel',
        );
    }
}
