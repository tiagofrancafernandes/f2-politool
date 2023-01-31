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
}
