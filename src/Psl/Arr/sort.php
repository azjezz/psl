<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Iter;

/**
 * Returns a new array sorted by the values of the given iterable. If the
 * optional comparator function isn't provided, the values will be sorted in
 * ascending order.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv>                $iterable
 * @psalm-param null|(callable(Tv, Tv): int)    $comparator
 *
 * @return array
 *
 * @psalm-return list<Tv>
 */
function sort(iterable $iterable, ?callable $comparator = null): array
{
    $arr = Iter\to_array_with_keys($iterable);
    if (null !== $comparator) {
        \usort($arr, $comparator);
    } else {
        \sort($arr);
    }

    return $arr;
}
