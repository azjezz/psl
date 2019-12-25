<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Iter;

/**
 * Returns a new array sorted by the keys of the given iterable. If the
 * optional comparator function isn't provided, the keys will be sorted in
 * ascending order.
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv> $iterable
 * @psalm-param null|(callable(Tk, Tk): int) $comparator
 *
 * @psalm-return array<Tk, Tv>
 */
function sort_by_key(iterable $iterable, ?callable $comparator = null): array
{
    $result = Iter\to_array_with_keys($iterable);
    if ($comparator) {
        \uksort($result, $comparator);
    } else {
        \ksort($result);
    }

    /** @psalm-var array<Tk, Tv> $result  */
    return $result;
}
