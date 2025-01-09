<?php

namespace App\Utils\SegmentFormatters;

use App\Traits\SegmentTrait;

abstract class BaseFormatter
{
    use SegmentTrait;

    protected function isSubConditionKeyTimeFrame($subCondition): bool
    {
        $timeframes = collect($this->getConditionsJson()['timeFrame']);
        return $timeframes->contains($subCondition['key']);
    }

    protected function isSubConditionKeyDuration($subCondition): bool
    {
        $durations = collect($this->getConditionsJson()['duration']);
        $durations->push('custom'); // 'custom' is not included in conditions
        return $durations->contains($subCondition['key']);
    }

    // =================================================================
    // Sub condition formatter (str)
    // =================================================================
    protected function formatSubConditionTimeFrame($subCondition): string
    {
        return "{$subCondition['key']} ";
    }

    protected function formatSubConditionDuration($subCondition): string
    {
        $durationKey = $subCondition['key'];
        $durationVal = $subCondition['value'];

        // duration between two datetime
        if ($durationKey === 'custom') {
            $from = $durationVal['from'];
            $to = $durationVal['to'];

            return "$from and $to";
        }

        $formattedDuration = $durationVal === 1
            ? substr($durationKey, 0, -1) // remove 's'
            : $durationKey;

        return "$durationVal $formattedDuration";
    }
    // ======================================
    // End sub condition formatter (str)
    // ======================================
}
