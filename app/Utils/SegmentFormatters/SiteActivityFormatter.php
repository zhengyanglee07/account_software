<?php

namespace App\Utils\SegmentFormatters;

use App\EcommercePage;
use App\funnel;
use App\Page;

class SiteActivityFormatter extends BaseFormatter implements IFormatter
{
    /**
     * @inheritDoc
     */
    public function format(array $subCondition): string
    {
        $conditions = $this->getConditionsJson();
        $siteActivitySub = $conditions['siteActivitySub'];
        $accuracy = $conditions['accuracy'];

        $key = $subCondition['key'];
        $value = $subCondition['value'] ?? '';

        // has visited a page / has not visited a page
        if (in_array($key, $siteActivitySub, true)) {
            return ': visitor ' . str_replace("a page", "", $key);
        }

        if (in_array($key, ['funnel', 'online-store', 'mini-store'])) {
            switch ($key) {
                case 'funnel':
                    return 'funnel ' . (funnel::find($value)?->funnel_name ?? '');
                    break;
                default:
                    return ucwords(implode(" ", explode("-", $key)));
            }
        }

        if ($key === 'builder-page') {
            if ($value === 'any') {
                return " - any landing pages ";
            } else {
                return ' - ' . (Page::find($value)?->name ?? '') . ' page ';
            }
        }

        if ($key === 'store-page') {
            if ($value === 'any') {
                return " - any pages ";
            } else {
                return ' - ' . (EcommercePage::find($value)?->name ?? '') . ' page ';
            }
        }

        // at least/at most/exactly
        if (in_array($key, $accuracy, true)) {
            return " $key $value times ";
        }

        // in the last/between/over all time
        if ($this->isSubConditionKeyTimeFrame($subCondition)) {
            return $this->formatSubConditionTimeFrame($subCondition);
        }

        // days/months/years + (date and date)
        if ($this->isSubConditionKeyDuration($subCondition)) {
            return $this->formatSubConditionDuration($subCondition);
        }

        // to cater for situation where key & value === null,
        // e.g. duration subCondition for 'over all time' timeframe
        return '';
    }
}
