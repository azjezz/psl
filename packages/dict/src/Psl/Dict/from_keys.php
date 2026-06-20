<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;

/**
 * Returns a dict where each value is the result of calling the given
 * function on the corresponding key.
 *
 * @param iterable<Tk> $keys
 * @param (Closure(Tk): Tv) $valueFunc
 *
 * @return array<Tk, Tv>
 *
 * @api
 */
function from_keys<Tk : int|string, Tv>(iterable $keys, Closure $valueFunc): array
{
    $result = [];
    foreach ($keys as $key) {
        $result[$key] = $valueFunc($key);
    }

    return $result;
}
