<?php

namespace App\Services\PeopleFilters;

use App\Traits\AuthAccountTrait;
use App\Traits\SegmentTrait;
use Carbon\Carbon;
use Illuminate\Support\Collection;

abstract class BaseFilter
{
    use AuthAccountTrait, SegmentTrait;

    // subConditions indexes
    protected const TOTAL_SALES_SUB = 0;
    protected const TOTAL_SALES_TIMEFRAME = 1;
    protected const TOTAL_SALES_DURATION = 2;

    protected const TAG_SUB = 0;

    protected const CUSTOM_FIELD_NAME = 0;
    protected const CUSTOM_FIELD_SELECT = 1;

    protected const PEOPLE_PROFILE_LABEL = 0;
    protected const PEOPLE_PROFILE_SELECT = 1;

    protected const FORM_SUBMISSION = 0;
    protected const FORM_SUBMISSION_TIMEFRAME = 1;
    protected const FORM_SUBMISSION_DURATION = 2;

    protected const PURCHASES_SUB = 0;
    protected const PURCHASES_PRODUCT_SELECT = 1;
    protected const PURCHASES_ACCURACY = 2;
    protected const PURCHASES_TIMEFRAME = 3;
    protected const PURCHASES_DURATION = 4;

    protected const ORDERS_SUB = 0;
    protected const ORDERS_TIMEFRAME = 1;
    protected const ORDERS_DURATION = 2;

    protected const AOV_SUB = 0;
    protected const AOV_TIMEFRAME = 1;
    protected const AOV_DURATION = 2;

    protected const VISIT_CONDITION = 0;
    protected const VISIT_SALES_CHANNEL = 1;
    protected const VISIT_PAGE = 2;
    protected const VISIT_FREQUENCY_PATTERN = 3;
    protected const VISIT_TIMEFRAME = 4;
    protected const VISIT_DURATION = 5;

    protected const MARKETING_EMAIL_STATUS_SUB = 0;

    // ======================================
    // General helpers
    // ======================================
    protected function isTimeFrameInTheLast($timeFrameKey): bool
    {
        return $timeFrameKey === 'in the last';
    }

    protected function isTimeFrameOverAllTime($timeFrameKey): bool
    {
        return $timeFrameKey === 'over all time';
    }

    protected function getDurationDate($durationKey, $durationValue): ?string
    {
        if ($durationKey === 'days') {
            return Carbon::now()
                ->subDays($durationValue)
                ->toDateTimeString();
        }

        if ($durationKey === 'months') {
            return Carbon::now()
                ->subMonths($durationValue)
                ->toDateTimeString();
        }

        if ($durationKey === 'years') {
            return Carbon::now()
                ->subYears($durationValue)
                ->toDateTimeString();
        }

        return null;
    }

    /**
     * Get orders based on time range (duration key/val).
     * $timeFrame and $duration are as of $subCondition's format
     *
     * @param Collection $orders Collection of Order
     * @param array $timeFrame Timeframe from filter's subCondition
     * @param array $duration Duration from filter's subCondition
     * @return mixed
     */
    protected function getOrdersInTimeframe(Collection $orders, array $timeFrame, array $duration)
    {
        $timeFrameKey = $timeFrame['key'];

        // do nothing if over all time, since it means take all
        if ($this->isTimeFrameOverAllTime($timeFrameKey)) {
            return $orders;
        }

        $durationKey = $duration['key'];
        $durationVal = $duration['value'];
        $isInTheLast = $this->isTimeFrameInTheLast($timeFrameKey);

        // formatting for "between" below is to enforce whereBetween
        // search between 2 full days. If we didn't put H:i:s
        // after Y-m-d, then the search will be based on 00:00:00
        // e.g. 2020-11-11 00:00:00 to 2020-11-15 00:00:00
        $datetimeBefore = $isInTheLast
            ? $this->getDurationDate($durationKey, $durationVal)
            : "{$durationVal['from']} 00:00:00";
        $datetimeAfter = $isInTheLast
            ? now()
            : "{$durationVal['to']} 23:59:59";

        return $orders->whereBetween(
            'created_at',
            [$datetimeBefore, $datetimeAfter]
        );
    }
    // ======================================
    // End general helpers
    // ======================================

    // ======================================
    // Debugger
    // ======================================
    /**
     * Simple debug function for testing
     *
     * @param Collection $contacts
     * @return Collection
     */
    protected function debugContacts(Collection $contacts): Collection
    {
        return $contacts->keys();
    }
    // ======================================
    // End debugger
    // ======================================
}
