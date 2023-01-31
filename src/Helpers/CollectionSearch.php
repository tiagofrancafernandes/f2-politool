<?php

namespace TiagoF2\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class CollectionSearch
{
    /**
     * @param  string|null  $key - Key to get value from collection (for non associative array keep as null)
     * @param  string  $expression
     * @param  bool|null  $ignoreCase = false
     * @return Collection|null
     */
    public static function filterLike(Collection $collectionData, string|null $key, string $expression, bool|null $ignoreCase = false): ?Collection
    {
        $expression = str_replace('%', '*', strtolower($expression));

        return $collectionData
            ->filter(function ($item) use ($key, $expression, $ignoreCase) {
                if ($ignoreCase) {
                    return (is_array($item) && Arr::isAssoc($item))
                        ? fnmatch($expression, ($item[$key] ?? ''), FNM_CASEFOLD)
                        : fnmatch($expression, ($item ?? ''), FNM_CASEFOLD);

                    return fnmatch($expression, ($item[$key] ?? ''), FNM_CASEFOLD);
                }

                return (is_array($item) && Arr::isAssoc($item))
                    ? fnmatch($expression, ($item[$key] ?? ''))
                    : fnmatch($expression, ($item ?? ''));

                return fnmatch($expression, ($item[$key] ?? ''));
            });
    }
}
