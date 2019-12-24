<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Iter;

/**
 * Returns a new array sorted by the values of the given iterable. If the
 * optional comparator function isn't provided, the values will be sorted in
 * ascending order.
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv> $iterable
 * @psalm-param null|(callable(Tv, Tv): int) $comparator
 *
 * @plsam-return array<Tk, Tv>
 */
function sort(iterable $iterable, ?callable $comparator = null): array
{
    $arr = Iter\to_array($iterable);
    if (null !== $comparator) {
        \usort($arr, $comparator);
    } else {
        \sort($arr);
    }

    return $arr;
}
