<?php

namespace App\Services\PeopleFilters;

use App\Traits\OrderTrait;
use Illuminate\Support\Collection;
use RuntimeException;

class AverageOrderValueFilter extends BaseFilter implements IFilter
{
    use OrderTrait;

    /**
     * @var array
     */
    private $subConditions;

    /**
     * @var array
     */
    private $aovSub;

    /**
     * @inheritDoc
     */
    public function filter(Collection $contacts, $subConditions): Collection
    {
        $this->subConditions = $subConditions;
        $this->aovSub = $this->getConditionsJson()['aovSub'];

        return $contacts->filter(function ($contact) {
            $orders = $this->getOrdersInTimeframe(
                $contact->orders,
                $this->subConditions[self::AOV_TIMEFRAME],
                $this->subConditions[self::AOV_DURATION]
            );

            return $this->compareAOV($this->calculateAOV($orders));
        });
    }

    /**
     * Compare aov against user-defined value in $subConditions
     *
     * @param float $aov Average Order Value
     * @return bool
     */
    private function compareAOV(float $aov): bool
    {
        $aovSubKey = $this->subConditions[self::AOV_SUB]['key'];
        $aovSubValue = $this->subConditions[self::AOV_SUB]['value'];

        // between
        // between must go first because its $aovSubValue is an assoc array
        if ($aovSubKey === $this->aovSub[3]) {
            $from = round($aovSubValue['from'], 2);
            $to = round($aovSubValue['to'], 2);

            return $aov > $from && $aov < $to;
        }

        $aovSubValue = round($aovSubValue, 2);

        // >=
        if ($aovSubKey === $this->aovSub[0]) {
            return $aov >= $aovSubValue;
        }

        // <=
        if ($aovSubKey === $this->aovSub[1]) {
            return $aov <= $aovSubValue;
        }

        // ===
        if ($aovSubKey === $this->aovSub[2]) {
            return $aov === $aovSubValue;
        }

        throw new RuntimeException("AOV sub key doesn't match");
    }
}
