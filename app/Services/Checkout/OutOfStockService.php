<?php

namespace App\Services\Checkout;

use App\UsersProduct;

use App\Repository\CheckoutRepository;

use App\Traits\SalesChannelTrait;

use Carbon\Carbon;

class OutOfStockService
{
    use SalesChannelTrait;

    // Original cart items with only basic info like id store in redis once checkout from cart
    protected $originalCartItems;
    // Processed cart items included detail like variant/combination detail 
    protected $cartItems;

    public function __construct()
    {
        $this->originalCartItems = CheckoutRepository::$cartItemFromRedis;
        $this->cartItems = CheckoutRepository::$allCartItems;
    }

    public function getOutOfStockProduct(): array
    {
        $outOfStockItems = [];
        foreach ($this->cartItems as $cartItem) {
            if (!$this->checkIsOutOfStock($cartItem)) continue;
            $outOfStockItems[] = $cartItem;
        }
        return $outOfStockItems;
    }

    public function checkIsOutOfStock($cartItem)
    {
        $originalItem = (object)$this->originalCartItems->firstWhere(function ($item) use ($cartItem) {
            $isSelected = $cartItem->reference_key === $item['reference_key'];
            if ($cartItem->hasVariant) $isSelected = $cartItem->variantRefKey === $item['variantRefKey'];
            return $isSelected;
        });
        $isActive = $cartItem->status === 'active';

        $isStockAvailable = $cartItem->is_selling || $cartItem->qty <= $cartItem->quantity;

        $isSameProductType = !!$originalItem->hasVariant === !!$cartItem->hasVariant;
        $isValidSalesChannel = in_array($this->getCurrentSalesChannel(), $cartItem->saleChannels);

        // Check for item variants
        $isVariantExists = true;
        $isVariantStockAvailable = true;
        $isVariantPurchasable = true;
        $isLatestVariants = true;
        $isLatestVariantValues = true;
        if (count($originalItem->combinationIds)) {
            $combinationIds = collect($originalItem->combinationIds);
            $selectedVariant = collect($cartItem->variant_details)->firstWhere(function ($variant, $index) use ($combinationIds) {
                return $combinationIds->every(function ($combId, $idx) use ($variant, $combinationIds) {
                    return $combinationIds->contains($variant['option_' . $idx + 1 . '_id']);
                });
            });

            $isVariantExists = isset($selectedVariant);
            if ($isVariantExists) {
                $isVariantStockAvailable = $selectedVariant['is_selling'] || $cartItem->qty <= $selectedVariant['quantity'];
                $isVariantPurchasable = $selectedVariant['is_visible'];
                $userProduct = UsersProduct::find($cartItem->id);
                $variants = $userProduct->variants();
                $variantValues = $userProduct->variant_values();
                $isLatestVariants = !$variants->where('variants.updated_at', '>', $cartItem->checkoutAt)->exists();
                $isLatestVariantValues = !$variantValues->where('variant_values.updated_at', '>', $cartItem->checkoutAt)->exists();
            }
        }

        // Check for item customizations
        $isLatestCustomization = true;
        $isCustomizationExists = true;
        $isCustomOptionsExisted = !isset($originalItem->customOption) || count($originalItem->customOption) === 0 || collect($cartItem->custom_options)->every(function ($option) use ($originalItem) {
            return in_array($option['id'], $originalItem->customOption);
        });

        if (count($originalItem->customizations ?? [])) {
            foreach ($originalItem->customizations as $customization) {
                if (!isset($cartItem->custom_options)) continue;
                $selectedOption = collect($cartItem->custom_options)->firstWhere('id', $customization['id']);
                $selectedOptionInput = optional(optional($selectedOption)['inputs'])->firstWhere(function ($input) use ($customization) {
                    return collect($customization['values'])->contains(function ($value) use ($input) {
                        return $value['id'] === $input->id;
                    });
                });
                if (isset($selectedOptionInput)) {
                    $oldUpdatedAt = collect($customization['values'])->firstWhere(function ($value) use ($selectedOptionInput) {
                        return $value['id'] === $selectedOptionInput->id;
                    })['updatedAt'];
                    $newUpdatedAt = $selectedOptionInput->updated_at;
                    $isLatestCustomization &= Carbon::parse($oldUpdatedAt)->greaterThanOrEqualTo($newUpdatedAt);
                } else {
                    $isCustomizationExists = false;
                }
            }
        }

        // Remove immediately from cart if match these condition
        $baseCondition =
            !$isLatestVariants ||
            !$isLatestVariantValues ||
            !$isLatestCustomization ||
            !$isCustomOptionsExisted ||
            !$isVariantExists ||
            !$isCustomizationExists ||
            !$isVariantPurchasable ||
            !$isSameProductType;
        // Let user manually remove in out of stock page if match these conditions
        $checkoutCondition =
            !$isActive ||
            ($originalItem->hasVariant ? !$isVariantStockAvailable : !$isStockAvailable) ||
            !$isValidSalesChannel ||
            $cartItem->isPurchased ||
            $cartItem->isEnrolled;

        $isCheckoutPage = str_contains(request()->path(), 'checkout');
        $isFunnel = CheckoutRepository::$isFunnel;
        return $baseCondition || (($isCheckoutPage || $isFunnel) ? $checkoutCondition : false);
    }
}
