<?php

namespace App\Traits;

use App\Traits\AuthAccountTrait;
use Illuminate\Support\Collection;

trait OrderTrait
{
    use CurrencyConversionTraits, AuthAccountTrait;

    /**
     * Get total sales of provided collection of orders
     *
     * @param Collection $orders
     * @return float
     */
    public function calculateOrdersTotalSales(Collection $orders, $accountId = null): float
    {
        $accountId = $orders->first()->account_id ?? null;
        return $this->getTotalPrice($orders->toArray(), false, $accountId);
    }

    /**
     * Calculate Average Order Value (AOV) of provided collection of orders
     *
     * AOV = S / n
     * where AOV = Average Order Value
     *       S = orders' total sales
     *       n = number of orders
     *
     * @param Collection $orders
     * @return float
     */
    public function calculateAOV(Collection $orders): float
    {
        $ordersTotalSales = $this->calculateOrdersTotalSales($orders);
        $ordersCount = $orders->count();
        return $ordersCount !== 0
            ? round($ordersTotalSales / $ordersCount, 2)
            : 0.0;
    }

    public function isContactInSegment($automation, $order)
    {
        return $automation->automationTriggers->contains(function ($at) use ($order) {
            $segment = $at->segment;
            $segmentIds = json_decode($order->segmentIds, true);
            if (!isset($segment)) return true;
            return in_array($segment->id, $segmentIds);
        });
    }
}
