<?php

namespace TiagoF2\StaticModel;

use TiagoF2\Helpers\CollectionSearch;
use TiagoF2\Helpers\StringHelpers;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class JsonStaticModel implements StaticModelInterface
{
    protected static bool|null $ignoreCache = false;

    protected static int $cacheTime = 60;

    protected static string $json_path = '';

    public function __construct(bool|null $ignoreCache = null)
    {
        static::$ignoreCache = $ignoreCache ?? false;
    }

    protected static function data()
    {
        if (!static::$json_path) {
            throw new \Exception('You must set the json_path property in your static model');
        }

        if (!file_exists(static::$json_path)) {
            throw new \Exception("File '" . static::$json_path . "' not found");
        }

        $cacheKey = StringHelpers::classNameSlug(self::class) . '-' . StringHelpers::classNameSlug(static::class);

        if (!static::$ignoreCache) {
            return collect(Cache::remember($cacheKey, static::$cacheTime /*secs*/, function () {
                return static::$json_path ? json_decode(file_get_contents(static::$json_path), true) ?? [] : [];
            }));
        }

        return collect(static::$json_path ? json_decode(file_get_contents(static::$json_path), true) ?? [] : []);
    }

    public static function all(): Collection
    {
        return static::data();
    }

    /**
     * @param  string  $key
     * @param  string  $expression
     * @param  bool|null  $ignoreCase = false
     * @return Collection|null
     */
    public static function search(string $key, string $expression, bool|null $ignoreCase = false): ?Collection
    {
        return CollectionSearch::filterLike(static::data(), $key, $expression, $ignoreCase);
    }

    public static function getBySlug(string $slug): ?Collection
    {
        foreach (static::data() as $item) {
            if ($item['slug'] === $slug) {
                return $item;
            }
        }

        return null;
    }

    public static function exists(int $id): bool
    {
        return isset(static::data()[$id]); //TODO
    }
}
