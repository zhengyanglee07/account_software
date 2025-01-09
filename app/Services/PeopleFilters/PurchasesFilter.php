<?php

namespace App\Services\PeopleFilters;

use Illuminate\Support\Collection;
use RuntimeException;

class PurchasesFilter extends BaseFilter implements IFilter
{
    /**
     * @var array
     */
    private $subConditions;

    /**
     * @var Collection
     */
    private $contacts;

    /**
     * @inheritDoc
     */
    public function filter(Collection $contacts, $subConditions): Collection
    {
        $this->subConditions = $subConditions;
        $this->contacts = $contacts;

        return $this
            ->subfilterProducts()
            ->subfilterAccuracy()
            ->getContacts();
    }

    // =====================================================================
    // Sub-filters
    // =====================================================================
    /**
     * Products sub-filter (specifically for 'Include Any' option on product select).
     *
     * Check all products associated to an order. If:
     * - purchases have been made, the AT LEAST ONE order detail must match the product
     * - purchases have not been made, then EVERY order details of the order must NOT
     *   match the product.
     *
     * Simply said, if one of the order details matches product, the order owner
     * is said to be at least purchased the product once in a specific time range
     *
     * *time range is not considered in this filter
     *
     * @return PurchasesFilter
     */
    private function subfilterProducts(): PurchasesFilter
    {
        $productSelectKey = $this->subConditions[self::PURCHASES_PRODUCT_SELECT]['key'];

        // do nothing if any products option is selected
        // since all products are included
        if ($productSelectKey === 'any') { // any products
            return $this;
        }

        $this->contacts = $this->contacts->filter(function ($contact) {
            $orderDetails = $this->flatPluckOrderDetails($contact->orders);

            // use collection's 'contains' method for "purchases have been made"
            // else use collection's 'every' method
            $purchasesMade = $this->purchasesHaveBeenMade();
            $fn = $purchasesMade ? 'contains' : 'every';

            return $orderDetails->$fn(
                function ($od) use ($purchasesMade) {
                    $c = $this->matchesOrderDetailProduct($od);
                    return $purchasesMade ? $c : !$c;
                }
            );
        });

        return $this;
    }

    /**
     * Accuracy sub-filter
     *
     * *there is small little hidden bug regarding timeframe, to be settled in the future
     *
     * @return PurchasesFilter
     */
    private function subfilterAccuracy(): PurchasesFilter
    {
        $this->contacts = $this->contacts->filter(function ($contact) {
            $purchasesMade = $this->purchasesHaveBeenMade();
            $accuracyKey = $this->subConditions[self::PURCHASES_ACCURACY]['key'];

            // note: int type casting here is important for strict comparison below
            $accuracyVal = (int)$this->subConditions[self::PURCHASES_ACCURACY]['value'];

            $orders = $this->getOrdersInTimeframe(
                $contact->orders,
                $this->subConditions[self::PURCHASES_TIMEFRAME],
                $this->subConditions[self::PURCHASES_DURATION]
            );
            $orderDetails = $this->flatPluckOrderDetails($orders);

            // extract only relevant products and sum up quantity
            $productQuantities = $orderDetails
                ->filter(function ($od) {
                    return $this->matchesOrderDetailProduct($od);
                })
                ->sum('quantity');

            switch ($accuracyKey) {
                case 'at least':
                    return $purchasesMade
                        ? $productQuantities >= $accuracyVal
                        : $productQuantities < $accuracyVal;

                case 'at most':
                    // 'at most' for purchases have not been made is quite awkward,
                    // hence the $productQuantities === 0. Maybe it should be removed
                    return $purchasesMade
                        ? $productQuantities <= $accuracyVal
                        : $productQuantities > $accuracyVal || $productQuantities === 0;

                case 'exactly':
                    return $purchasesMade
                        ? $productQuantities === $accuracyVal
                        : $productQuantities !== $accuracyVal;

                default:
                    throw new RuntimeException('Please provide correct accuracy key');
            }
        });

        return $this;
    }

    // =====================================================================
    // Helpers
    // =====================================================================
    /**
     * Check whether purchases have/have not been made
     *
     * @return bool
     */
    private function purchasesHaveBeenMade(): bool
    {
        $purchasesSubKey = $this->subConditions[self::PURCHASES_SUB]['key'];
        return $purchasesSubKey === 'have been made';
    }

    /**
     * Match order detail's product by using productOptionKey & val in subConditions field.
     *
     * Note:
     * If both [productOptionKey & productOptionVal are null], which will happen
     * if "Any Products" product select option (frontend 3rd dropdown) is selected,
     * this func will return true for all NOT NULL order details.
     *
     * @param $orderDetail
     * @return bool
     */
    private function matchesOrderDetailProduct($orderDetail): bool
    {
        [$productOptionKey, $productOptionVal] = $this->getProductOptionsKV();

        $c = strcasecmp(
            $orderDetail->usersProduct->$productOptionKey,
            $productOptionVal
        );

        return $c === 0;
    }

    /**
     * @return Collection
     */
    private function getContacts(): Collection
    {
        return $this->contacts;
    }

    /**
     * Simple func to get product options key & val
     *
     * @return array
     */
    private function getProductOptionsKV(): array
    {
        $productSelectVal = $this->subConditions[self::PURCHASES_PRODUCT_SELECT]['value'];

        return [
            $productSelectVal['key'],  // users_products related property
            $productSelectVal['value'] // property value
        ];
    }

    /**
     * Pluck and flatten order details from orders
     *
     * @param Collection $orders
     * @return Collection
     */
    private function flatPluckOrderDetails(Collection $orders): Collection
    {
        return $orders
            ->pluck('orderDetails')
            ->flatten(1);
    }
}
