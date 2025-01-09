<?php

namespace App\Utils\SegmentFormatters;

class SegmentFormatterFactory
{
    public static function createFormatter(string $conditionName): IFormatter
    {
        if ($conditionName === 'Total Sales') {
            return new TotalSalesFormatter;
        }

        if ($conditionName === 'Tags') {
            return new TagsFormatter;
        }

        if ($conditionName === 'Custom Field') {
            return new CustomFieldFormatter;
        }

        if ($conditionName === 'People Profile') {
            return new PeopleProfileFormatter;
        }

        if ($conditionName === 'Site Activity') {
            return new SiteActivityFormatter;
        }

        if ($conditionName === 'Form Submission') {
            return new FormSubmissionFormatter;
        }

        if ($conditionName === 'Purchases') {
            return new PurchasesFormatter;
        }

        if ($conditionName === 'Orders') {
            return new OrdersFormatter;
        }

        if ($conditionName === 'Average Order Value') {
            return new AverageOrderValueFormatter;
        }

        if ($conditionName === 'Marketing Email Status') {
            return new MarketingEmailStatusFormatter;
        }
    }
}
