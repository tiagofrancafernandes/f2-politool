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
}
