<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;

use function is_array;
use function ksort;
use function uksort;

/**
 * Returns a new dict sorted by the keys of the given iterable.
 *
 * If the optional comparator function isn't provided, the keys will be sorted in
 * ascending order.
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (Closure(Tk, Tk): int)|null $comparator
 *
 * @return array<Tk, Tv>
 *
 * @api
 */
function sort_by_key<Tk : int|string = int|string, Tv = mixed>(iterable $iterable, null|Closure $comparator = null): array
{
    if (is_array($iterable)) {
        $result = $iterable;
    } else {
        $result = [];
        foreach ($iterable as $k => $v) {
            $result[$k] = $v;
        }
    }

    if ($comparator) {
        uksort($result, $comparator);
        return $result;
    }

    ksort($result);
    return $result;
}
