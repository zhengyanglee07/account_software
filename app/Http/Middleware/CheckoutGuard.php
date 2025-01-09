<?php

namespace App\Http\Middleware;

use App\Traits\SalesChannelTrait;
use App\Account;
use App\EcommercePreferences;
use App\Repository\CheckoutRepository;
use App\Services\Checkout\CheckoutErrorService;
use App\Services\Checkout\FormServices;
use App\Services\Checkout\OutOfStockService;
use App\Services\Checkout\ShippingService;
use App\Services\RedisService;

use Carbon\Carbon;

use Closure;

class CheckoutGuard
{
    use SalesChannelTrait;

    protected $accountId, $currentSaleChannel, $lastAccessedAt;

    public function __construct()
    {
        $this->accountId = $this->getCurrentAccountId();
        $this->currentSaleChannel = $this->getCurrentSalesChannel();
        $this->lastAccessedAt = RedisService::get('lastAccessedAt');
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $currentPath = trim($request->path(), 'api/v1/');

        // Skip checking for checkout success page
        $isCheckoutSuccessPage = preg_match("/checkout\/data\/success\/[0-9]+/i", $currentPath);
        if ($isCheckoutSuccessPage) {
            return $next($request);
        }

        $isOrderProcessPage =  str_contains($currentPath, 'checkout/order/process');
        if ($isOrderProcessPage) {
            $paymentRef = RedisService::get('paymentReferences');
            if (empty($paymentRef)) abort(404);

            $returnUrl = $this->checkIsCurrentSalesChannel('mini-store') ? 'checkout/data/mini-store' : 'checkout/data/payment';
            if (request()->redirect_status === 'failed') {
                RedisService::set('paymentError', 'Failed FPX payment');
                return abort(302, $returnUrl);
            };

            return $next($request);
        }

        if (in_array($currentPath, ['checkout/data/information', 'checkout/data/mini-store'])) {
            RedisService::set('lastAccessedAt', Carbon::now());
        }

        if (!$this->isActiveSalesChannel()) abort(404);

        if (!$this->hasCartItems()) return abort(302, '/shopping/cart');


        if ($this->hasOutOfStockItems()) {
            if ($currentPath !== 'checkout/data/outOfStock')
                return abort(302, '/checkout/outOfStock');
            return $next($request);
        }

        // Check accessible for online store
        if ($this->checkIsCurrentSalesChannel('online-store')) {

            // Always redirect to customer information page if preferences updated
            $isPageToCheckPreferences = in_array($currentPath, ['checkout/data/shipping', 'checkout/data/payment']);
            if ($isPageToCheckPreferences && $this->isEcommercePreferenceUpdated()) {
                CheckoutErrorService::setError(CheckoutErrorService::PREFERENCES_UPDATED);
                return abort(302, '/checkout/information');
            }

            $isPageToCheckShipping = in_array($currentPath, ['checkout/payment']);
            $isShippingRequired = (new FormServices())->isShippingRequired();
            $hasSelectedShippingMethod = ShippingService::hasSelectedShippingMethod();
            if ($isPageToCheckShipping && $isShippingRequired && !$hasSelectedShippingMethod) {
                CheckoutErrorService::setError(CheckoutErrorService::SHIPPING_METHOD_NOT_SELECTED);
                return abort(302, '/checkout/shipping');
            }
        }

        return $next($request);
    }

    private function isActiveSalesChannel()
    {
        $activeSaleChannels = Account::find($this->accountId)->activeSaleChannels();
        $activeSaleChannels =  $activeSaleChannels->pluck('type')->toArray();
        return in_array($this->currentSaleChannel, $activeSaleChannels);
    }

    private function hasCartItems()
    {
        return CheckoutRepository::$cartItemFromRedis->isNotEmpty();
    }

    private function hasOutOfStockItems()
    {
        return count((new OutOfStockService())->getOutOfStockProduct());
    }

    private function isEcommercePreferenceUpdated()
    {
        return !EcommercePreferences::where('account_id', $this->accountId)
            ->where('updated_at', '<=', $this->lastAccessedAt)
            ->exists();
    }
}
