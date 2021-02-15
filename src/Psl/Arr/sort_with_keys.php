<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Dict;

/**
 * Returns a new array sorted by the values of the given array. If the
 * optional comparator function isn't provided, the values will be sorted in
 * ascending order ( maintains index association ).
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv>                  $array
 * @psalm-param (callable(Tv, Tv): int)|null   $comparator
 *
 * @psalm-return array<Tk, Tv>
 *
 * @deprecated use `Dict\sort` instead
 *
 * @see Dict\sort()
 */
function sort_with_keys(array $array, ?callable $comparator = null): array
{
    return Dict\sort($array, $comparator);
}
