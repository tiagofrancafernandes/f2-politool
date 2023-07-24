<?php

use TiagoF2\Helpers\StringHelpers;

if (!function_exists('classNameSlug')) {
    /**
     * @param  string  $className
     * @return string|null
     */
    function classNameSlug(string $className): string|null
    {
        return StringHelpers::classNameSlug($className);
    }
}

if (!function_exists('spf')) {
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
    function spf(string $firstString, ...$params): string
    {
        return StringHelpers::spf($firstString, ...$params);
    }
}
