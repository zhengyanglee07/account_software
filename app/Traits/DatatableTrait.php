<?php

namespace App\Traits;

trait DatatableTrait
{
    public function paginateRecords($records, $query, $columns = [], $fn = null)
    {
        if (($query['search'] ?? null) && isset($columns)) {
            $records = $records->where(function ($queryBuilder) use ($query, $columns) {
                if (is_array($columns) && count($columns) > 0) {
                    foreach ($columns as $column) {
                        $queryBuilder->orWhere($column, 'like', "%{$query['search']}%");
                    }
                } else {
                    $queryBuilder->where($columns, 'like', "%{$query['search']}%");
                }
            });
        }

        $records = $records->orderBy(
            $query['column'] ?? 'created_at',
            $query['order'] ?? 'desc'
        )->paginate(20);

        return $fn
            ? $records->through($fn)
            : $records;
    }
}
