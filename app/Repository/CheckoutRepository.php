<?php

namespace App\Repository;

use App\Repository\Checkout\CheckoutData;

use App\Services\Checkout\CartService;
use App\Services\Checkout\CashbackService;
use App\Services\Checkout\PromotionService;
use App\Services\Checkout\StoreCreditService;
use App\Services\Checkout\TaxService;

use App\Traits\Checkout\CheckoutOrderTrait;
use App\Traits\UsersProductTrait;

class CheckoutRepository
{
    use CheckoutOrderTrait, UsersProductTrait;


    public static $allProducts;
    public static $allCartItems, $cartItemFromRedis, $cartItems, $hasPhysicalProduct, $totalWeight;
    public static $taxableProductTotal, $subTotalWithoutPromotion, $subTotal, $grandTotal, $grandTotalWithoutStoreCredit;
    public static $cartItemsWithPromotion, $availablePromotions, $groupedPromotions;
    public static $cashback, $storeCredit;
    public static $taxSettngs;
    public static $isFunnel = false;

    public function __construct()
    {
        new CheckoutData();

        if (request()->isLanding) {
            self::$isFunnel = true;
        }

        self::$allProducts = $this->getAllActiveProducts($this->getCurrentAccountId());
        $cartService = new CartService();
        $cartService->setCartItems();
        self::$cartItemFromRedis = $cartService->cartItemFromRedis;
        self::$allCartItems = $cartService->cartItems; // included draft product and product not for current sales channel
        self::$cartItems = $cartService->getProductOfCurrentSalesChannel(self::$allCartItems);
        self::$hasPhysicalProduct = $cartService->hasPhysicalProduct();
        self::$totalWeight = $cartService->getTotalWeight() ?? 0;

        self::$subTotalWithoutPromotion = $this->calculateSubtotal(false);

        $promotionService = new PromotionService();
        $promotionService->setAvailablePromotion();
        self::$availablePromotions = $promotionService->availablePromotions;
        self::$groupedPromotions = $promotionService->getGroupedPromotion();

        self::$cartItemsWithPromotion = $cartService->getCartItemWithPromotion(self::$groupedPromotions['product']);

        self::$subTotal = $this->calculateSubtotal();

        self::$taxSettngs = (new TaxService)->getTaxSetting();
        self::$taxableProductTotal = $cartService->getTotalTaxableProductPrice(self::$cartItemsWithPromotion);
        self::$grandTotalWithoutStoreCredit = $this->calculateGrandTotal();
        self::$storeCredit = (new StoreCreditService)->getStoreCreditToBeUsed();
        self::$grandTotal = self::$grandTotalWithoutStoreCredit - (self::$isFunnel ? 0 : self::$storeCredit);
        self::$cashback = (new CashbackService())->getMostBeneficialCashback();
    }

    public static function setIsFunnel($isFunnel)
    {
        self::$isFunnel = $isFunnel;
    }

    public static function setCartItem()
    {
        new CheckoutData();
        $cartService = new CartService();
        $cartService->setCartItems();
        self::$cartItemFromRedis = $cartService->cartItemFromRedis;
        self::$allCartItems = $cartService->cartItems; // included draft product and product not for current sales channel
        self::$cartItems = $cartService->getProductOfCurrentSalesChannel(self::$allCartItems);
        self::$hasPhysicalProduct = $cartService->hasPhysicalProduct();
        self::$totalWeight = $cartService->getTotalWeight() ?? 0;
    }
}
