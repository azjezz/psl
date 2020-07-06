<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Iter;

/**
 * Merges multiple iterables into a new array. In the case of duplicate
 * keys, later values will overwrite the previous ones.
 *
 * Example:
 *      Arr\merge([1, 2], [9, 8])
 *      => Arr(0 => 9, 1 => 8)
 *
 *      Arr\merge([0 => 1, 1 => 2], [2 => 9, 3 => 8])
 *      => Arr(0 => 1, 1 => 2, 2 => 9, 3 => 8)
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param    iterable<Tk, Tv> $first
 * @psalm-param    iterable<iterable<Tk, Tv>> $rest
 * @psalm-return   array<Tk, Tv>
 */
function merge(iterable $first, iterable ...$rest): array
{
    /** @psalm-var array<Tk, Tv> $first */
    $first = Iter\to_array_with_keys($first);
    /** @psalm-var list<iterable<Tk, Tv>> $rest */
    $rest = Iter\to_array($rest);

    /** @psalm-var list<iterable<Tk, Tv>> $arrays */
    $arrays = [$first, ...$rest];

    return flatten($arrays);
}
