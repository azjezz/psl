<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Dict;

/**
 * Applies a mapping function to all values of an iterable.
 *
 * The function is passed the current iterable value and should return a
 * modified array value.
 *
 * The key is left as-is and not passed to the mapping function.
 *
 * Examples:
 *
 *     Arr\map([1, 2, 3, 4, 5], fn($i) => $i * 2);
 *     => Arr(2, 4, 6, 8, 10)
 *
 * @template Tk of array-key
 * @template Tv
 * @template T
 *
 * @param iterable<Tk, Tv> $iterable Iterable to be mapped over
 * @param (callable(Tv): T) $function
 *
 * @return array<Tk, T>
 *
 * @deprecated use `Dict\map` instead.
 * @see Dict\map()
 */
function map(iterable $iterable, callable $function): array
{
    return Dict\map($iterable, $function);
}
