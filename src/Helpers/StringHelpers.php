<?php

namespace TiagoF2\Helpers;

use Illuminate\Support\Str;

class StringHelpers
{
    /**
     * @param  string  $className
     * @return string|null
     */
    public static function classNameSlug(string $className): string|null
    {
        $className = (strpos($className, '\\') != false)
            ? substr(strrchr($className, '\\'), 1)
            : $className;
        $className = str_replace('__', '_', $className);

        return Str::snake($className); // OperationEnum -> operation_enum
    }

    /**
     * spf function  Easy way to use sprintf
     *
    *
    * ```php
    * spf('aa %s %d', 123, 34); // "aa 123 34"
    * ```
    *
    * @param string $firstString
    * @param float|int|string ...$params
    *
    * @return string
     */
    public static function spf(string $firstString, ...$params): string
    {
        // For new versions of PHP this is more easy. Only float|int|string ...$params

        $params = array_filter(array_values($params), function ($item) {
            return is_numeric($item) || is_string($item);
        });

        return sprintf($firstString, ...$params);
    }
}
