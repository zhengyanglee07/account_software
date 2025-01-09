<?php

namespace App\Services\PeopleFilters;

use Illuminate\Support\Collection;

interface IFilter
{
    /**
     * @param \Illuminate\Support\Collection $contacts
     * @param $subConditions
     * @return \Illuminate\Support\Collection
     */
    public function filter(Collection $contacts, $subConditions): Collection;
}
