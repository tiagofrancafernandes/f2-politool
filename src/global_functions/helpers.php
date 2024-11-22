<?php

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use TiagoF2\Expansions\CollectionExpansion;
use TiagoF2\Helpers\StringHelpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

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

if (!function_exists('class_name_as_slug')) {
    /**
     * @param  string  $className
     * @return string|null
     */
    function class_name_as_slug(string $className): string|null
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

if (!function_exists('str_or_null')) {
    /**
     * function str_or_null
     *
     * @param mixed $value
     *
     * @return ?string
     */
    function str_or_null(mixed $value): ?string
    {
        return filter_var($value, FILTER_DEFAULT, FILTER_NULL_ON_FAILURE);
    }
}

if (!function_exists('is_a_in')) {
    /**
     * function is_a_in
     *
     * @param mixed $value
     *
     * @return bool
     */
    function is_a_in(mixed $value, array $classes): bool
    {
        $stringOrObject = fn ($v) => is_object($value) || is_string($value);

        if (!$stringOrObject($value)) {
            return false;
        }

        $classes = array_filter($classes, $stringOrObject);

        if (!$classes) {
            return false;
        }

        foreach ($classes as $classe) {
            if (is_a($value, $classe, true)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('date_or_null')) {
    /**
     * function date_or_null
     *
     * @param mixed $value
     *
     * @return ?\Illuminate\Support\Carbon
     */
    function date_or_null(mixed $value): ?Illuminate\Support\Carbon
    {
        try {
            if (is_null($value) || !filled($value)) {
                return null;
            }

            if (is_string($value)) {
                preg_match(
                    '/^((sub|add){1}\ ([0-9]){1,}\ ){1}(second|minute|hour|day|week|month|year){1}(s){0,1}/i',
                    $value,
                    $match
                );

                $value = $match && count($match) >= 3 ? str_replace(
                    [
                        'sub', // -
                        'add', // +
                    ],
                    [
                        '-', //'sub',
                        '+', //'add',
                    ],
                    strval($match[0])
                ) : $value;
            }

            if (is_string($value)) {
                return is_numeric($value)
                    ? Illuminate\Support\Carbon::parse(intval($value))
                    : Illuminate\Support\Carbon::parse($value);
            }

            if (
                is_a_in($value, [
                    Carbon\Month::class,
                    Carbon\Carbon::class,
                    Carbon\WeekDay::class,
                    DateTime::class,
                    DateTimeImmutable::class,
                    DateTimeInterface::class,
                    Illuminate\Support\Carbon::class,
                ])
            ) {
                return Illuminate\Support\Carbon::parse($value);
            }

            return Illuminate\Support\Carbon::parse($value);
        } catch (Throwable $th) {
            return null;
        }
    }
}

if (!function_exists('try_str_or_null')) {
    /**
     * function try_str_or_null
     *
     * @param mixed $value
     *
     * @return ?string
     */
    function try_str_or_null(mixed $value): ?string
    {
        try {
            if (is_array($value)) {
                return json_encode($value, 64);
            }

            return str_or_null($value) ?? (string) $value;
        } catch (Throwable $th) {
            return null;
        }
    }
}

if (!function_exists('url_or_null')) {
    /**
     * function url_or_null
     *
     * @param mixed $value
     * @param ?string $protocol
     *
     * @return ?string
     */
    function url_or_null(mixed $value, ?string $protocol = null): ?string
    {
        $value = str_or_null($value);

        if (!$value) {
            return null;
        }

        $url = filter_var(
            $value,
            FILTER_VALIDATE_URL,
            FILTER_NULL_ON_FAILURE
        );

        if ($url) {
            return $url;
        }

        return filter_var(
            sprintf('%s%s', $protocol ?: '', $value),
            FILTER_VALIDATE_URL,
            FILTER_NULL_ON_FAILURE
        ) ?: null;
    }
}

if (!function_exists('domain_or_null')) {
    /**
     * function domain_or_null
     *
     * @param mixed $value
     *
     * @return ?string
     * @param ?string $protocol
     */
    function domain_or_null(mixed $value, ?string $protocol = null, bool $withTld = true): ?string
    {
        $value = str_or_null($value);

        if (!$value) {
            return null;
        }

        $fullURL = url_or_null($value, $protocol ?: 'https://');

        $result = $fullURL ? parse_url($fullURL, PHP_URL_HOST) : null;

        if (!$withTld) {
            return $result;
        }

        return str_contains($result, '.'); // TODO: melhorar
    }
}

if (!function_exists('int_or_null')) {
    /**
     * function int_or_null
     *
     * @param mixed $value
     *
     * @return ?int
     */
    function int_or_null(mixed $value): ?int
    {
        return filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    }
}

if (!function_exists('file_head_lines')) {
    /**
     * function file_head_lines
     *
     * @param string $filePath
     * @param int $lines = 5
     * @param bool $toString
     * @param ?string $separator
     *
     * @return array|string
     */
    function file_head_lines(
        string $filePath,
        int $lines = 5,
        bool $toString = true,
        ?string $separator = PHP_EOL,
    ): array|string {
        if (!is_file($filePath)) {
            throw new Exception(sprintf('Invalid file "%s"', $filePath));
        }

        $handle = fopen($filePath, 'r');

        if (!$handle) {
            throw new Exception('Fail on open file');
        }

        $counter = 0;
        $content = [];

        while (!feof($handle) && $counter < $lines) {
            $line = fgets($handle);

            if ($line === false) {
                break;
            }

            $content[] = $line;
            $counter++;
        }

        fclose($handle);

        return $toString ? implode($separator ?? PHP_EOL, $content) : $content;
    }
}

if (!function_exists('bool_or_null')) {
    /**
     * function bool_or_null
     *
     * @param mixed $value
     *
     * @return ?bool
     */
    function bool_or_null(mixed $value): ?bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
    }
}

if (!function_exists('save_if_is_dirty')) {
    /**
     * function save_if_is_dirty
     *
     * @param ?Model $toCheck
     *
     * @return ?Model
     */
    function save_if_is_dirty(?Model $toCheck): ?Model
    {
        if (!$toCheck || !$toCheck?->isDirty()) {
            return $toCheck;
        }

        $toCheck?->save();

        return $toCheck;
    }
}

if (!function_exists('try_collect')) {
    /**
     * function try_collect
     *
     * @param mixed $data
     *
     * @return null|EloquentCollection|Collection
     */
    function try_collect(
        mixed $data = null,
        ?Closure $catch = null
    ): null|EloquentCollection|Collection {
        try {
            if (
                is_object($data) &&
                (is_a($data, Collection::class) || is_a($data, EloquentCollection::class))
            ) {
                return $data;
            }

            return is_iterable($data) ? collect($data) : null;
        } catch (Throwable $th) {
            if ($catch) {
                $catch($th);
            }

            return null;
        }
    }
}

if (!function_exists('is_any_collection')) {
    /**
     * function is_any_collection
     *
     * @param mixed $data
     *
     * @return bool
     */
    function is_any_collection(
        mixed $data = null,
        ?Closure $catch = null
    ): bool {
        try {
            if (
                is_object($data) &&
                (is_a($data, Collection::class) || is_a($data, EloquentCollection::class))
            ) {
                return true;
            }

            return false;
        } catch (Throwable $th) {
            if ($catch) {
                $catch($th);
            }

            return false;
        }
    }
}

if (!function_exists('is_collection')) {
    /**
     * function is_collection
     *
     * @param mixed $data
     *
     * @return bool
     */
    function is_collection(
        mixed $data = null,
        ?Closure $catch = null
    ): bool {
        try {
            if (is_object($data) && is_a($data, Collection::class)) {
                return true;
            }

            return false;
        } catch (Throwable $th) {
            if ($catch) {
                $catch($th);
            }

            return false;
        }
    }
}

if (!function_exists('array_is_equal_to')) {
    /**
     * function array_is_equal_to
     *
     * @param array $array1
     * @param array $array2
     *
     * @return bool
     */
    function array_is_equal_to(array $array1, array $array2): bool
    {
        // Verifica se ambos os arrays têm o mesmo número de elementos
        if (count($array1) !== count($array2)) {
            return false;
        }

        // Se os arrays são explicitamente iguais
        if ($array1 !== $array2) {
            return false;
        }

        // Verifica se ambos os arrays têm as mesmas chaves
        if (array_keys($array1) !== array_keys($array2)) {
            return false;
        }

        // Verifica se ambos os arrays têm os mesmos valores na mesma ordem
        foreach ($array1 as $key => $value) {
            if (($array2[$key] ?? null) !== $value) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('array_in_array')) {
    /**
     * function array_in_array
     *
     * @param array $array1
     * @param array $array2
     *
     * @return bool
     */
    function array_in_array(array $array1, array $array2): bool
    {
        if (!$array1 || !$array2 || (count($array1) > count($array2))) {
            return false;
        }

        // Verifica se cada item de array1 está presente no array2
        foreach ($array1 as $item) {
            if (!in_array($item, $array2)) {
                return false; // Retorna false assim que um item não é encontrado
            }
        }

        return true;
    }
}

if (! function_exists('f2_request')) {
    /**
     * Get an instance of the current request or an input item from the request.
     *
     * @param  list<string>|string|null  $key
     * @param  mixed  $default
     * @return ($key is null ? \Illuminate\Http\Request|\Symfony\Component\HttpFoundation\Request : ($key is string ? mixed : array<string, mixed>))
     */
    function f2_request($key = null, $default = null)
    {
        /** @var Request|Symfony\Component\HttpFoundation\Request $request */
        $request = new Request(
            // array $query = []
            // array $request = []
            // array $attributes = []
            // array $cookies = []
            // array $files = []
            // array $server = []
            // $content = null
        );

        if (is_null($key)) {
            return $request;
        }

        if (is_array($key)) {
            return $request->only($key);
        }

        $value = $request->__get($key);

        return is_null($value) ? value($default) : $value;
    }
}

if (! function_exists('request')) {
    /**
     * Get an instance of the current request or an input item from the request.
     *
     * @param  list<string>|string|null  $key
     * @param  mixed  $default
     * @return ($key is null ? \Illuminate\Http\Request|\Symfony\Component\HttpFoundation\Request : ($key is string ? mixed : array<string, mixed>))
     */
    function request($key = null, $default = null)
    {
        return f2_request($key, $default);
    }
}

if (!function_exists('is_embed')) {
    /**
     * function is_embed
     *
     * @param ?Request $request
     * @return bool
     */
    function is_embed(?Request $request = null): bool
    {
        $request ??= request();

        return bool_or_null($request?->input('embed') ?? $request?->input('iframe')) ?? false;
    }
}

if (!function_exists('numbers_only')) {
    /**
     * function numbers_only
     *
     * @param mixed $value
     *
     * @return string
     */
    function numbers_only(mixed $value): string
    {
        $value = is_numeric($value) || is_string($value) || is_a($value, Stringable::class) ? strval($value) : '';

        return preg_replace('/\D/', '', $value);
    }
}

if (!function_exists('f2_to_string_or_null')) {
    /**
     * function f2_to_string_or_null
     *
     * @param mixed $value
     * @param null|Closure $catch
     *
     * @return ?string
     */
    function f2_to_string_or_null(mixed $value, null|Closure $catch = null): ?string
    {
        try {
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

if (!function_exists('to_string_or_null')) {
    /**
     * function to_string_or_null
     *
     * @param mixed $value
     * @param null|Closure $catch
     *
     * @return ?string
     */
    function to_string_or_null(mixed $value, null|Closure $catch = null): ?string
    {
        return f2_to_string_or_null($value, $catch);
    }
}

if (!function_exists('to_string')) {
    /**
     * function to_string
     *
     * @param mixed $value
     *
     * @return string
     */
    function to_string(mixed $value): string
    {
        return (string) f2_to_string_or_null($value);
    }
}

if (!function_exists('f2_chain')) {
    /**
     * function f2_chain
     *
     * @param mixed $object
     * @param string $chainNotation
     * @param mixed $default
     *
     * `// f2_chain(str('Tiago França'), 'slug.camel')` // tiagoFranca
     * `// f2_chain(str('Tiago França'), 'slug->camel')` // tiagoFranca
     * `// f2_chain(str('Tiago França'), 'slug?->camel')` // tiagoFranca
     *
     * @return mixed
     */
    function f2_chain(mixed $object, string $chainNotation, mixed $default = null): mixed
    {
        if (is_null($object)) {
            return $default;
        }

        if (!is_object($object)) {
            return $object;
        }

        $chainNotation = collect(explode(
            '_NOTATION_',
            str_replace([
                '?->',
                '->',
                '|',
                '::',
                '=>',
                '.'
            ], '_NOTATION_', $chainNotation)
        ))->map(fn ($item) => trim($item));
        // ->filter(fn($item) => filled($item))->toArray() // Talvez seja melhor dar erro em caso de invalid chain

        if (!$chainNotation) {
            return $object;
        }

        $validKey = fn ($value) => preg_match('/^([a-zA-Z_]){1}([a-zA-Z0-9_]){0,}$/', $value) > 0;
        $noArgsMethod = fn ($value) => preg_match('/^([a-zA-Z_]){1}([a-zA-Z0-9_]){0,}\(\)$/', $value) > 0;

        $newObject = $object;

        foreach ($chainNotation as $toCall) {
            if (is_null($newObject)) {
                continue;
            }

            $isMethod = $noArgsMethod($toCall);

            if ($isMethod) {
                $newObject = $newObject?->{$toCall}() ?? $default;

                continue;
            }

            if (!$isMethod && !$validKey($toCall)) {
                // invalid chain
            }

            $newObject = $newObject?->{$toCall} ?? $default;
        }

        return $newObject ?? $default;
    }
}

if (!function_exists('chain')) {
    /**
     * alias to `f2_chain` function
     *
     * @param mixed $object
     * @param string $chainNotation
     * @param mixed $default
     *
     * `// chain(str('Tiago França'), 'slug.camel')` // tiagoFranca
     * `// chain(str('Tiago França'), 'slug->camel')` // tiagoFranca
     * `// chain(str('Tiago França'), 'slug?->camel')` // tiagoFranca
     *
     * @return mixed
     */
    function chain(mixed $object, string $chainNotation, mixed $default = null): mixed
    {
        return f2_chain($object, $chainNotation, $default);
    }
}

if (! function_exists('collect')) {
    /**
     * Create a collection from the given value.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>|null  $value
     * @return \Illuminate\Support\Collection<TKey, TValue>
     */
    function collect($value = []): Collection|CollectionExpansion
    {
        return new CollectionExpansion($value);
    }
}

if (!function_exists('expanded_collection')) {
    /**
     * function expanded_collection
     *
     * @param mixed $value
     *
     * @return Collection
     */
    function expanded_collection(mixed $value = null): Collection|CollectionExpansion
    {
        $value = is_null($value) ? [] : $value;
        $value = is_iterable($value) ? $value : ['data' => $value];

        /** @var Collection $collection */
        $collection = new CollectionExpansion($value);

        return $collection;
    }
}

if (!function_exists('f2_collect')) {
    /**
     * alias to `expanded_collection`
     *
     * @param mixed $value
     *
     * @return Collection
     */
    function f2_collect(mixed $value = null): Collection|CollectionExpansion
    {
        return expanded_collection($value);
    }
}

if (!function_exists('xcollect')) {
    /**
     * alias to `expanded_collection`
     *
     * @param mixed $value
     *
     * @return Collection
     */
    function xcollect(mixed $value = null): Collection|CollectionExpansion
    {
        return expanded_collection($value);
    }
}
