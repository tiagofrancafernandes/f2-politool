<?php

namespace TiagoF2\Helpers;

use Illuminate\Database\Eloquent\Builder;

class QueryFormater
{
    /**
     * @param  Builder  $query
     * @param  array  $ids
     * @param  ?string $idCollumn
     *
     * @return Builder
     */
    public static function idRange(Builder $query, array $ids, ?string $idCollumn = 'id'): Builder
    {
        if (!$ids) {
            return $query;
        }

        $idCollumn = $idCollumn ?: 'id';

        $firstParam = $ids[0] ?? null;
        $secondParam = $ids[1] ?? null;

        if (count($ids) === 2 && ($secondParam > $firstParam)) {
            return $query->where($idCollumn, '>=', $firstParam)->where($idCollumn, '<=', $secondParam);
        }

        return $query->whereIn($idCollumn, $ids);
    }
}
