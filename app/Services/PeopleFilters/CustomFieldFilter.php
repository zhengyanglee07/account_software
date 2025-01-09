<?php

namespace App\Services\PeopleFilters;

use Illuminate\Support\Collection;

class CustomFieldFilter extends BaseFilter implements IFilter
{
    /**
     * @inheritDoc
     */
    public function filter(Collection $contacts, $subConditions): Collection
    {
        $customSelect = $this->getConditionsJson()['customSelect'];
        $customFieldSelectKey = $subConditions[self::CUSTOM_FIELD_SELECT]['key'];
        $customFieldSelectValue = $subConditions[self::CUSTOM_FIELD_SELECT]['value'];
        $customFieldName = $subConditions[self::CUSTOM_FIELD_NAME]['value'];

        $contactsWithRelatedCustomField = $contacts
            ->filter(static function ($contact) use ($customFieldName) {
                $customFields = $contact->peopleCustomFields;

                return $customFields->contains(static function ($customField) use ($customFieldName) {
                    return $customField->peopleCustomFieldName->custom_field_name === $customFieldName;
                });
            });

        $customFieldCondition = [
            'isSet' => $customFieldSelectKey === $customSelect[0],
            'isNotSet' => $customFieldSelectKey === $customSelect[1],
            'is' => $customFieldSelectKey === $customSelect[2],
            'isNot' => $customFieldSelectKey === $customSelect[3],
            'isLessThanOrEqualTo' => $customFieldSelectKey === $customSelect[4],
            'isGreaterThanOrEqualTo' => $customFieldSelectKey === $customSelect[5],
        ];

        if ($customFieldCondition['isSet']) {
            return $contactsWithRelatedCustomField;
        }

        if ($customFieldCondition['isNotSet']) {
            return $contacts->diff($contactsWithRelatedCustomField)->keyBy('id');
        }

        return $contactsWithRelatedCustomField
            ->filter(static function ($contact) use (
                $customFieldName,
                $customFieldSelectValue,
                $customFieldCondition
            ) {
                $customField = $contact
                    ->peopleCustomFields
                    ->firstWhere(
                        'peopleCustomFieldName.custom_field_name',
                        $customFieldName
                    );
                $content = $customField->custom_field_content;

                if ($customFieldCondition['is']) {
                    return $content === $customFieldSelectValue;
                }

                if ($customFieldCondition['isNot']) {
                    return $content !== $customFieldSelectValue;
                }

                if ($customFieldCondition['isLessThanOrEqualTo']) {
                    if (!is_numeric($content)) return false;
                    return (float)$content <= (float)$customFieldSelectValue;
                }

                if ($customFieldCondition['isGreaterThanOrEqualTo']) {
                    if (!is_numeric($content)) return false;
                    return (float)$content >= (float)$customFieldSelectValue;
                }

                return false;
            });
    }
}
