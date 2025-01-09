<?php

namespace App\Utils\SegmentFormatters;

class TotalSalesFormatter extends BaseFormatter implements IFormatter
{
    /**
     * @inheritDoc
     */
    public function format(array $subCondition): string
    {
        if ($this->isSubConditionKeyTimeFrame($subCondition)) {
            return $this->formatSubConditionTimeFrame($subCondition);
        }

        if ($this->isSubConditionKeyDuration($subCondition)) {
            return $this->formatSubConditionDuration($subCondition);
        }

        // total sales sub (is greater than../is less than.../exactly)
        // or where key & value === null, e.g. duration for over all time
        return $subCondition['key'] . ' ' . $subCondition['value'] . ' ';
    }
}
