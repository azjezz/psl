<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Dict;

/**
 * Applies a mapping function to all values of an iterable.
 *
 * The function is passed the current iterable key and value and should return a
 * modified array value.
 *
 * The key is left as-is.
 *
 * Examples:
 *
 *     Arr\map_with_key([1, 2, 3, 4, 5], fn($k, $v) => $k + $v);
 *     => Arr(1, 3, 5, 7, 9)
 *
 * @template Tk of array-key
 * @template Tv
 * @template T
 *
 * @param iterable<Tk, Tv> $iterable Iterable to be mapped over
 * @param (callable(Tk,Tv): T) $function
 *
 * @return array<Tk, T>
 *
 * @deprecated use `Dict\map_with_key` instead.
 * @see Dict\map_with_key()
 */
function map_with_key(iterable $iterable, callable $function): array
{
    return Dict\map_with_key($iterable, $function);
}
