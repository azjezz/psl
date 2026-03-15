<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;

/**
 * Returns a dict where each value is the result of calling the given
 * function on the corresponding key.
 *
 * @template Tk as array-key
 * @template Tv
 *
 * @param iterable<Tk> $keys
 * @param (Closure(Tk): Tv) $valueFunc
 *
 * @return array<Tk, Tv>
 */
function from_keys(iterable $keys, Closure $valueFunc): array
{
    $result = [];
    foreach ($keys as $key) {
        $result[$key] = $valueFunc($key);
    }

    return $result;
}
