<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class PaginatorService
{
    public static function getPaginatedData(Builder $model, $mapCallback = null, $path = null, $perPage = 150)
    {
        $currentPage = request()?->query('page') ?? 1;
        $paginatedData = $model->paginate($perPage, ['*'], 'page', $currentPage);

        $mappedData = $paginatedData->getCollection();
        if ($mapCallback) $mappedData = $mappedData->map($mapCallback);

        return new LengthAwarePaginator(
            $mappedData,
            $paginatedData->total(),
            $perPage,
            $currentPage,
            ['path' => $path ?? LengthAwarePaginator::resolveCurrentPath()]
        );
    }
}
