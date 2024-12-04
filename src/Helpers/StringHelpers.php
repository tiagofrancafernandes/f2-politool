<?php

namespace TiagoF2\Helpers;

use Illuminate\Support\Str;
use Throwable;
use Closure;

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

        $params = array_values($params);

        foreach ($params as $key => $item) {
            $params[$key] = trim(var_export($item, true), "'");
        }

        return sprintf($firstString, ...array_values($params));
    }

    /**
     * function tryToString
     *
     * @param mixed $value
     * @param null|Closure $catch
     *
     * @return ?string
     */
    public static function tryToString(mixed $value, null|Closure $catch = null): ?string
    {
        try {
            if (is_null($value)) {
                return null;
            }

            if (is_object($value) && is_a($value, Closure::class)) {
                $value = $value();
            }

            if (is_string($value) || is_null($value)) {
                return $value;
            }

            if (is_array($value)) {
                return json_encode($value, 64);
            }

            if (is_object($value) && method_exists($value, 'toJson')) {
                return (string) $value?->toJson();
            }

            if (is_object($value) && method_exists($value, 'toArray')) {
                return json_encode($value?->toArray(), 64);
            }

            if (is_object($value) && method_exists($value, '__toString')) {
                return (string) $value?->__toString();
            }

            if (is_object($value) && method_exists($value, 'toString')) {
                return (string) $value?->toString();
            }

            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            }

            if (is_numeric($value)) {
                return (string) $value;
            }

            return (string) $value;
        } catch (Throwable $th) {
            if ($catch) {
                try {
                    $catch($th);
                } catch (Throwable $th) {
                    //
                }
            }

            return null;
        }
    }
}
