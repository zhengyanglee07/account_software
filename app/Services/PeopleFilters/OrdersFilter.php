<?php

namespace App\Services\PeopleFilters;

use Illuminate\Support\Collection;
use RuntimeException;

class OrdersFilter extends BaseFilter implements IFilter
{
    /**
     * @var array
     */
    private $subConditions;

    /**
     * @var array
     */
    private $ordersSub;

    /**
     * @inheritDoc
     */
    public function filter(Collection $contacts, $subConditions): Collection
    {
        $this->subConditions = $subConditions;
        $this->ordersSub = $this->getConditionsJson()['ordersSub'];

        return $contacts->filter(function ($contact) {
            $ordersCount = $this
                ->getOrdersInTimeframe(
                    $contact->orders,
                    $this->subConditions[self::ORDERS_TIMEFRAME],
                    $this->subConditions[self::ORDERS_DURATION]
                )
                ->count();

            return $this->compareOrdersCount($ordersCount);
        });
    }

    /**
     * Simple func to calculate orders count against
     * user-defined val in $subConditions
     *
     * @param int $ordersCount
     * @return bool
     */
    private function compareOrdersCount(int $ordersCount): bool
    {
        $ordersSubKey = $this->subConditions[self::ORDERS_SUB]['key'];
        $ordersSubValue = $this->subConditions[self::ORDERS_SUB]['value'];

        // between
        // between must go first because its $ordersSubValue is an assoc array
        if ($ordersSubKey === $this->ordersSub[3]) {
            $from = (int)$ordersSubValue['from'];
            $to = (int)$ordersSubValue['to'];

            return $ordersCount > $from && $ordersCount < $to;
        }

        // this int type casting is important for strict comparison below
        $ordersSubValue = (int)$ordersSubValue;

        // >=
        if ($ordersSubKey === $this->ordersSub[0]) {
            return $ordersCount >= $ordersSubValue;
        }

        // <=
        if ($ordersSubKey === $this->ordersSub[1]) {
            return $ordersCount <= $ordersSubValue;
        }

        // ===
        if ($ordersSubKey === $this->ordersSub[2]) {
            return $ordersCount === $ordersSubValue;
        }

        throw new RuntimeException("Orders sub key doesn't match");
    }
}
