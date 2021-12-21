<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;

use function ksort;
use function uksort;

/**
 * Returns a new dict sorted by the keys of the given iterable.
 *
 * If the optional comparator function isn't provided, the keys will be sorted in
 * ascending order.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (Closure(Tk, Tk): int)|null $comparator
 *
 * @return array<Tk, Tv>
 */
function sort_by_key(iterable $iterable, ?Closure $comparator = null): array
{
    $result = [];
    foreach ($iterable as $k => $v) {
        $result[$k] = $v;
    }

    if ($comparator) {
        uksort($result, $comparator);
    } else {
        ksort($result);
    }

    return $result;
}
