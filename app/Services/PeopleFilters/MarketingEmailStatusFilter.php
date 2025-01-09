<?php

namespace App\Services\PeopleFilters;

use Illuminate\Support\Collection;

class MarketingEmailStatusFilter extends BaseFilter implements IFilter
{
    /**
     * @inheritDoc
     */
    public function filter(Collection $contacts, $subConditions): Collection
    {
        $sub = $this->getConditionsJson()['marketingEmailStatusSub'];
        $statusSubKey = $subConditions[self::MARKETING_EMAIL_STATUS_SUB]['key'];
        $statusSubValue = $subConditions[self::MARKETING_EMAIL_STATUS_SUB]['value'];
        $isHasStatus = $statusSubKey === $sub[0];

        return $contacts
            ->filter(static function ($contact) use ($statusSubValue, $isHasStatus) {
                $status = strtolower($contact->marketingEmailStatus());

                return $isHasStatus
                    ? $status === $statusSubValue
                    : $status !== $statusSubValue;
            });
    }
}