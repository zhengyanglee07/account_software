<?php

namespace App\Repository\Checkout;

use App\Currency;
use App\Models\Promotion\Promotion;
use App\Services\RedisService;
use App\Services\Checkout\ShippingService;
use App\Traits\AuthAccountTrait;
use Illuminate\Support\Facades\Log;

class CheckoutData
{
    use AuthAccountTrait;

    /**
     * Datas based on customer when checkout
     *
     * Use all static data here after initialize by call 'new CheckoutData()'
     */
    public static $formDetail;
    public static $shippingOption;
    public static $cartItems;
    public static $remark;
    public static $shippingMethod;
    public static $shippingMethodDetail;
    public static $paymentMethodId;
    public static $currency;
    public static $promotion;

    public function __construct()
    {
        self::$formDetail = new FormDetail();
        self::$shippingOption = (object)RedisService::get('shippingOption');
        self::$cartItems = collect(RedisService::get('cartItems'))->where(function ($item) {
            return !empty($item['reference_key']);
        });
        self::$remark = RedisService::get('remark');
        self::$shippingMethod = (object)RedisService::get('shippingMethod');
        self::$promotion = Promotion::whereIn('id', RedisService::get('promotion') ?? [])->get();

        // TODO: Set currency according to selected currency by customer
        self::$currency = Currency::where('account_id', $this->getCurrentAccountId())->where('isDefault', 1)->first();

        self::$paymentMethodId = RedisService::get('paymentMethod');
    }


    /**
     * Get and set detail of shipping methods
     *
     * ! This methods will take times for (easyparcel, lalamove, delyva) shipping methods !
     *
     * @return void
     */
    public static function setSelectedShippingMethodDetail()
    {
        $shippingService = new ShippingService();
        self::$shippingMethodDetail = $shippingService->getSelectedShippingMethodDetail();
    }
}
