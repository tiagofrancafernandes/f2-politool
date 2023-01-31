<?php

namespace TiagoF2\StaticModel;

use Illuminate\Support\Collection;

interface StaticModelInterface
{
    public static function all(): Collection;

    public static function search(string $key, string $expression, bool|null $ignoreCase = false): ?Collection;

    public static function exists(int $id): bool;
}
