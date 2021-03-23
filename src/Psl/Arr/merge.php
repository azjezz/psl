<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Dict;

/**
 * Merges multiple iterables into a new array.
 *
 * In the case of duplicate keys, later values will overwrite the previous ones.
 *
 * Example:
 *      Arr\merge([1, 2], [9, 8])
 *      => Arr(0 => 9, 1 => 8)
 *
 *      Arr\merge([0 => 1, 1 => 2], [2 => 9, 3 => 8])
 *      => Arr(0 => 1, 1 => 2, 2 => 9, 3 => 8)
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv> $first
 * @param iterable<Tk, Tv> ...$rest
 *
 * @return array<Tk, Tv>
 *
 * @deprecated use `Dict\merge` instead.
 * @see Dict\merge()
 *
 * @no-named-arguments
 */
function merge(iterable $first, iterable ...$rest): array
{
    /** @var list<iterable<Tk, Tv>> $iterables */
    $iterables = [$first, ...$rest];

    return Dict\flatten($iterables);
}
