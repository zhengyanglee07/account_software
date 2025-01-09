<?php

namespace App\Traits\Automations;

use App\Automation;
use App\Order;

trait TriggerableProduct
{
    /**
     * Check if usersProductIds provided in order upon trigger match
     * users_product_id in AutomationPurchaseProductTrigger
     *
     * @param Automation $automation
     * @param Order $order
     * @return bool
     */
    protected function matchUsersProductId($automation, Order $order): bool
    {
        return $automation->automationTriggers->contains(function ($at) use ($order) {
            $ppTrigger = $at->automationPurchaseProductTrigger;

            if (!$ppTrigger) {
                return false;
            }

            $usersProductId = $ppTrigger->users_product_id;
            $usersProductIds = $order->orderDetails->pluck('users_product_id');

            // null usersProductId means "Any" product, which matches any usersProduct
            return is_null($usersProductId) || $usersProductIds->contains($usersProductId);
        });
    }
}
