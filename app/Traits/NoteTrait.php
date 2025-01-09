<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Collection;

trait NoteTrait
{
    /**
     * Get a multidimensional collection notes grouped by
     * created_at date (Note: date only, time is excluded)
     *
     * @param \Illuminate\Database\Eloquent\Collection $notes
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getGroupedNotesByDate($notes): Collection
    {
        return $notes
            ->sortByDesc('created_at')
            ->groupBy(static function ($note) {
                return $note->created_at->toDateString();
            });
    }
}
