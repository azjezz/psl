<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Returns a new array sorted by the values of the given array.
 *
 * If the optional comparator function isn't provided, the values will be sorted in
 * ascending order.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv>                       $array
 * @psalm-param null|(pure-callable(Tv, Tv): int)   $comparator
 *
 * @return array
 *
 * @psalm-return list<Tv>
 */
function sort(array $array, ?callable $comparator = null): array
{
    if (null !== $comparator) {
        \usort($array, $comparator);
    } else {
        \sort($array);
    }

    return $array;
}
