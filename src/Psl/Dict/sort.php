<?php

declare(strict_types=1);

namespace Psl\Dict;

use function asort;
use function uasort;

/**
 * Returns a new dict ( keyed-array ) sorted by the values of the given iterable.
 *
 * If the optional comparator function isn't provided, the values will be sorted
 * in ascending order.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (callable(Tv, Tv): int)|null $comparator
 *
 * @return array<Tk, Tv>
 */
function sort(iterable $iterable, ?callable $comparator = null): array
{
    $array = [];
    foreach ($iterable as $k => $v) {
        $array[$k] = $v;
    }

    if (null !== $comparator) {
        uasort($array, $comparator);
    } else {
        asort($array);
    }

    return $array;
}
