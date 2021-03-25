<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Dict;

/**
 * Returns a new array sorted by the keys of the given array. If the
 * optional comparator function isn't provided, the keys will be sorted in
 * ascending order.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param array<Tk, Tv> $array
 * @param (callable(Tk, Tk): int)|null $comparator
 *
 * @return array<Tk, Tv>
 *
 * @deprecated use `Dict\sort_by_key`
 * @see Dict\sort_by_key
 */
function sort_by_key(array $array, ?callable $comparator = null): array
{
    return Dict\sort_by_key($array, $comparator);
}
