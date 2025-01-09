<?php

namespace App\Services\PeopleFilters;

use Illuminate\Support\Collection;

class TagsFilter extends BaseFilter implements IFilter
{
    /**
     * @inheritDoc
     */
    public function filter(Collection $contacts, $subConditions): Collection
    {
        $tagsSub = $this->getConditionsJson()['tagSub'];
        $tagSubKey = $subConditions[self::TAG_SUB]['key'];
        $tagSubValue = $subConditions[self::TAG_SUB]['value'];
        $isHaveTags = $tagSubKey === $tagsSub[0];

        return $contacts
            ->filter(static function ($contact) use ($tagSubValue, $isHaveTags) {
                $tags = $contact->processed_tags;
                $tagsFoundCount = $tags->whereIn('tagName', $tagSubValue)->count();

                return $isHaveTags
                    ? $tagsFoundCount === count($tagSubValue)
                    : $tagsFoundCount !== count($tagSubValue);
            });
    }
}
