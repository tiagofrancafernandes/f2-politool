<?php

namespace TiagoF2\Expansions;

use Illuminate\Support\Collection;

class CollectionExpansion extends Collection
{
    /**
     * function __get
     *
     * @param $key
     * @return
     */
    public function __get($key)
    {
        return $this->get($key, null) ?? null;
    }

    public static function initCollectionMacros(): void
    {
        if (!Collection::hasMacro('expansion')) {
            Collection::macro('expansion', function (): Collection {
                /** @var Collection $collection */
                $collection = new CollectionExpansion($this);

                return $collection;
            });
        }

        if (!Collection::hasMacro('optional')) {
            Collection::macro('optional', function (mixed $key = null, mixed $default = null): mixed {
                /** @var Collection $collection */
                $collection = new CollectionExpansion($this);

                if (!is_null($key)) {
                    return $collection->get($key, $default);
                }

                return $collection;
            });
        }

        if (!Collection::hasMacro('valuesToUpper')) {
            Collection::macro('valuesToUpper', function (): Collection {
                /** @var Collection $collection */
                $collection = $this;

                return $collection->map(fn ($value) => strtoupper($value));
            });
        }

        if (!Collection::hasMacro('keysToUpper')) {
            Collection::macro('keysToUpper', function (): Collection {
                /** @var Collection $collection */
                $collection = $this;

                return $collection->mapWithKeys(fn ($value, $key) => [strtoupper($key) => $value]);
            });
        }

        if (!Collection::hasMacro('strOnKeys')) {
            Collection::macro('strOnKeys', function (string $method, ...$args): Collection {
                /** @var Collection $collection */
                $collection = $this;

                return $collection->mapWithKeys(
                    function ($value, $key) use ($method, $args): array {
                        $key = to_string_or_null(str($key)->{$method}(...$args)) ?? $key;

                        return [$key => $value];
                    }
                );
            });
        }

        if (!Collection::hasMacro('strOnValues')) {
            Collection::macro('strOnValues', function (string $method, ...$args): Collection {
                /** @var Collection $collection */
                $collection = $this;

                return $collection->mapWithKeys(
                    fn ($value, $key) => [$key => str($value)->{$method}(...$args)]
                );
            });
        }

        if (!Collection::hasMacro('str')) {
            Collection::macro('str', function (callable $callback): Collection {
                /** @var Collection $collection */
                $collection = $this;

                $fn = fn ($value, $key) => [$key => $callback(str($value), $key)];

                return $collection->mapWithKeys($fn);
            });
        }
    }
}
