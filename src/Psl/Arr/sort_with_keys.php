<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Returns a new array sorted by the values of the given array. If the
 * optional comparator function isn't provided, the values will be sorted in
 * ascending order ( maintains index association ).
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv>                       $array
 * @psalm-param null|(pure-callable(Tv, Tv): int)   $comparator
 *
 * @psalm-return array<Tk, Tv>
 *
 * @psalm-pure
 */
function sort_with_keys(array $array, ?callable $comparator = null): array
{
    if (null !== $comparator) {
        \uasort($array, $comparator);
    } else {
        \asort($array);
    }

    return $array;
}
