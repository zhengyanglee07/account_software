<?php

namespace App\Services\PeopleFilters;

use App\Currency;
use App\Traits\OrderTrait;
use Illuminate\Support\Collection;

class TotalSalesFilter extends BaseFilter implements IFilter
{
    use OrderTrait;

    /**
     * @var Collection
     */
    private $subConditions;

    /**
     * @inheritDoc
     */
    public function filter(Collection $contacts, $subConditions): Collection
    {
        $this->subConditions = $subConditions;
        $accountId = $contacts->first()->account_id ?? null;

        if (!$accountId) {
            return collect();
        }

        return $contacts->filter(function ($contact) use ($accountId){
            $orders = $this->getOrdersInTimeframe(
                $contact->orders,
                $this->subConditions[self::TOTAL_SALES_TIMEFRAME],
                $this->subConditions[self::TOTAL_SALES_DURATION]
            );

            return $this->compareOrderTotalSales(
                round($this->calculateOrdersTotalSales($orders, $accountId), 2),
                $this->getOperator(),
                $this->subConditions[self::TOTAL_SALES_SUB]['value']
            );
        });
    }

    /**
     * @return string
     */
    private function getOperator(): string
    {
        $totalSalesSub = $this->getConditionsJson()['totalSalesSub'];
        $totalSalesSubKey = $this->subConditions[self::TOTAL_SALES_SUB]['key'];

        if ($totalSalesSubKey === $totalSalesSub[0]) {
            return '>=';
        }

        if ($totalSalesSubKey === $totalSalesSub[1]) {
            return '<=';
        }

        if ($totalSalesSubKey === $totalSalesSub[2]) {
            return '=';
        }

        if ($totalSalesSubKey === $totalSalesSub[3]) {
            return '><';
        }

        throw new \RuntimeException('Operator not found');
    }

    private function compareOrderTotalSales($ordersTotalSales, $operator, $totalSalesValue): bool
    {
        if ($operator === '><') {
            $from = (float)$totalSalesValue['from'];
            $to = (float)$totalSalesValue['to'];

            return $ordersTotalSales > $from && $ordersTotalSales < $to;
        }

        $floatTotalSalesValue = (float)$totalSalesValue;

        if ($operator === '<=') {
            return $ordersTotalSales <= $floatTotalSalesValue;
        }

        if ($operator === '>=') {
            return $ordersTotalSales >= $floatTotalSalesValue;
        }

        return $ordersTotalSales === $floatTotalSalesValue;
    }
}
