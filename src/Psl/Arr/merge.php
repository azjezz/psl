<?php

declare(strict_types=1);

namespace Psl\Arr;

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
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @psalm-param    iterable<Tk, Tv> $first
 * @psalm-param    iterable<iterable<Tk, Tv>> $rest
 * @psalm-return   array<Tk, Tv>
 */
function merge(iterable $first, iterable ...$rest): array
{
    /** @psalm-var array<Tk, Tv> */
    return flatten([$first, ...$rest]);
}
