<?php

namespace App\Services;

use App\ProcessedContact;
use App\Services\PeopleFilters\PeopleFilterFactory;
use App\Traits\SegmentTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class SegmentService
{
    use SegmentTrait;

    // ======================================
    // Segment queries
    // ======================================
    /**
     * Keyed contacts that is required by filterContacts processing
     *
     * @param int|null $accountId
     * @return \Illuminate\Support\Collection
     */
    public function getKeyedContacts($accountId = null): Collection
    {
        return ProcessedContact
            ::where('account_id', $accountId ?? Auth::user()->currentAccountId)
            ->with(
                'peopleCustomFields.peopleCustomFieldName',
                'processed_tags',
                'orders.orderDetails.usersProduct'
            )
            ->get()
            ->keyBy('id');
    }
    // ======================================
    // End cached queries
    // ======================================

    // ======================================
    // guards
    // ======================================
    public function isSubConditionKeyTimeFrame($subCondition): bool
    {
        $timeframes = collect($this->getConditionsJson()['timeFrame']);
        return $timeframes->contains($subCondition['key']);
    }

    public function isSubConditionKeyDuration($subCondition): bool
    {
        $durations = collect($this->getConditionsJson()['duration']);
        return $durations->contains($subCondition['key']);
    }
    // ======================================
    // End guards
    // ======================================

    /**
     * Get name of all segments in a contact
     *
     * @param $contact
     * @param $segments
     * @return array
     */
    public function getContactSegmentNames($contact, $segments): array
    {
        $segmentNames = [];

        foreach ($segments as $segment) {
            if ($segment->hasContact($contact->id)) {
                $segmentNames[] = $segment->segmentName;
            }
        }

        return $segmentNames;
    }

    /**
     * Core filter func. Used to filter contacts included in a
     * specific segment
     *
     * IMPORTANT: you should ALWAYS provide $contacts as second
     *            param if you're iterating through segments
     *
     * @param mixed $conditionFilters conditions in segment
     * @param \Illuminate\Support\Collection|null $contacts Keyed contacts
     * @param bool $id whether to return processed contacts ids
     * @return array|\Illuminate\Support\Collection
     */
    public function filterContacts($conditionFilters, ?Collection $contacts = null, $id = false, $accountId = null)
    {
        $resultContacts = collect();
        $currentAccountContacts = $contacts ?? $this->getKeyedContacts($accountId);

        foreach ($conditionFilters as $ORCondition) {
            // remember to reset contacts on every OR loop
            $filteredContacts = $currentAccountContacts;

            foreach ($ORCondition as $ANDCondition) {
                $firstConditionName = $ANDCondition['name'];
                $subConditions = $ANDCondition['subConditions'];

                if ($filteredContacts->count() === 0) break;

                $filteredContacts = PeopleFilterFactory
                    ::createFilter($firstConditionName)
                    ->filter($filteredContacts, $subConditions);
            }

            $resultContacts = $resultContacts->union($filteredContacts);
        }

        return $id ? $resultContacts->keys()->toArray() : $resultContacts->values();
    }
}
