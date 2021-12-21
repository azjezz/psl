<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;

/**
 * Returns a dict where each value is the result of calling the given
 * function on the corresponding key.
 *
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk> $keys
 * @param (Closure(Tk): Tv) $value_func
 *
 * @return array<Tk, Tv>
 */
function from_keys(iterable $keys, Closure $value_func): array
{
    $result = [];
    foreach ($keys as $key) {
        $result[$key] = $value_func($key);
    }

    return $result;
}
