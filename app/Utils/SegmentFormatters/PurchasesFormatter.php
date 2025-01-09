<?php

namespace App\Utils\SegmentFormatters;

class PurchasesFormatter extends BaseFormatter implements IFormatter
{
    /**
     * @inheritDoc
     */
    public function format(array $subCondition): string
    {
        $conditions = $this->getConditionsJson();
        $purchasesSub = $conditions['purchasesSub'];
        $productOptions = $conditions['productOptions'];
        $accuracy = $conditions['accuracy'];

        $key = $subCondition['key'];
        $value = $subCondition['value'];

        // have been/have not been made
        if (in_array($key, $purchasesSub, true)) {
            return "$key on ";
        }

        // at least/at most/exactly
        if (in_array($key, $accuracy, true)) {
            return "$key $value times ";
        }

        // in the last/between/over all time
        if ($this->isSubConditionKeyTimeFrame($subCondition)) {
            return $this->formatSubConditionTimeFrame($subCondition);
        }

        // days/months/years + (date and date)
        if ($this->isSubConditionKeyDuration($subCondition)) {
            return $this->formatSubConditionDuration($subCondition);
        }

        // any products
        if ($key === 'any') {
            return "any products ";
        }

        // some products
        if ($key === 'some') {
            $productOptionKey = strtolower($productOptions[$value['key']]);
            $productOptionValue = collect($value['value'])->implode(', ');

            return "products with $productOptionKey $productOptionValue ";
        }

        // to cater for situation where key & value === null,
        // e.g. duration subCondition for 'over all time' timeframe
        return '';
    }
}
