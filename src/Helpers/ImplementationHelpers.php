<?php

namespace TiagoF2\Helpers;

class ImplementationHelpers
{
    public const NOT_IMPLEMMENTED = 789;

    /**
     * @param  string  $targetClassName
     * @param  string|array  $interfaces
     * @return void
     */
    public static function validateImplementation(
        string $targetClassName,
        string|array $interfaces
    ): void {
        $interfaces = is_array($interfaces) ? array_values($interfaces) : [$interfaces];

        if (array_diff($interfaces, class_implements($targetClassName)) == $interfaces) {
            static::implementationThrowException($targetClassName, $interfaces);
        }
    }

    /**
     * implementationThrowException function
     *
     * @param  string  $targetClassName
     * @param  string|array  $interfaces
     * @return void
     */
    public static function implementationThrowException(
        string $targetClassName,
        string|array $interfaces
    ) {
        $interfaces = is_string($interfaces) ? $interfaces : implode("\n- ", $interfaces);

        throw new \Exception(
            sprintf("The '%s' class must implements some of this interfaces:\n- %s\n", $targetClassName, $interfaces),
            self::NOT_IMPLEMMENTED
        );
    }
}
