<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Dict;

/**
 * Returns a new array sorted by the keys of the given array. If the
 * optional comparator function isn't provided, the keys will be sorted in
 * ascending order.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv>                       $array
 * @psalm-param (callable(Tk, Tk): int)|null        $comparator
 *
 * @psalm-return array<Tk, Tv>
 *
 * @deprecated use `Dict\sort_by_key`
 *
 * @use Dict\sort_by_key
 */
function sort_by_key(array $array, ?callable $comparator = null): array
{
    return Dict\sort_by_key($array, $comparator);
}
