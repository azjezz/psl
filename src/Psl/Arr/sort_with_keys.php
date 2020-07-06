<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Iter;

/**
 * Returns a new array sorted by the values of the given iterable. If the
 * optional comparator function isn't provided, the values will be sorted in
 * ascending order ( maintains index association ).
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv>                $iterable
 * @psalm-param null|(callable(Tv, Tv): int)    $comparator
 *
 * @plsam-return array<Tk, Tv>
 */
function sort_with_keys(iterable $iterable, ?callable $comparator = null): array
{
    $arr = Iter\to_array_with_keys($iterable);
    if (null !== $comparator) {
        \uasort($arr, $comparator);
    } else {
        \asort($arr);
    }

    return $arr;
}
