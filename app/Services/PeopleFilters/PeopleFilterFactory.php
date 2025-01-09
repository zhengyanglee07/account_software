<?php

namespace App\Services\PeopleFilters;

class PeopleFilterFactory
{
    public static function createFilter(string $conditionName): IFilter
    {
        if ($conditionName === 'Total Sales') {
            return new TotalSalesFilter;
        }

        if ($conditionName === 'Tags') {
            return new TagsFilter;
        }

        if ($conditionName === 'Custom Field') {
            return new CustomFieldFilter;
        }

        if ($conditionName === 'People Profile') {
            return new PeopleProfileFilter;
        }

        if ($conditionName === 'Site Activity') {
            return new SiteActivityFilter;
        }

        if ($conditionName === 'Form Submission') {
            return new FormSubmissionFilter;
        }

        if ($conditionName === 'Purchases') {
            return new PurchasesFilter;
        }

        if ($conditionName === 'Orders') {
            return new OrdersFilter;
        }

        if ($conditionName === 'Average Order Value') {
            return new AverageOrderValueFilter;
        }

        if ($conditionName === 'Marketing Email Status') {
            return new MarketingEmailStatusFilter;
        }
    }
}
