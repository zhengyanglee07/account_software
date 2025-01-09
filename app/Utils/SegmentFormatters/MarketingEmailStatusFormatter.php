<?php


namespace App\Utils\SegmentFormatters;


class MarketingEmailStatusFormatter extends BaseFormatter implements IFormatter
{
    /**
     * @inheritDoc
     */
    public function format(array $subCondition): string
    {
        $status = ucfirst($subCondition['value']);
        return "{$subCondition['key']} {$status}";
    }
}