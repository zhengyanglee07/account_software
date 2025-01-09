<?php

namespace App\Traits;

use App\Services\ContactCurrencyService;
use App\Services\SegmentService;

/**
 * Add contacts functionalities to Segment model
 *
 * @package App\Traits
 *
 */
trait SegmentContactsTrait
{
    /**
     * @param bool $id whether to return processed contact ids
     * @return array|\Illuminate\Support\Collection
     */
    public function contacts($id = false)
    {
        $segmentService = resolve(SegmentService::class);
        $contactCurrencyService = resolve(ContactCurrencyService::class);

        $contacts = $segmentService->filterContacts(
            $this->conditions,
            $segmentService->getKeyedContacts($this->account_id),
            $id
        );

        if ($id) {
            return $contacts;
        }

        return $contactCurrencyService->mapContactsValuesBasedOnCurrency($contacts, $this->account_id);
    }

    public function hasContact($contactId): bool
    {
        return $this->contacts()->pluck('id')->contains($contactId);
    }
}
