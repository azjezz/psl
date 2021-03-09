<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Dict;

/**
 * Applies a mapping function to all keys of an iterable.
 *
 * The function is passed the current iterable key and should return a
 * modified iterable key.
 *
 * The value is left as-is and not passed to the mapping function.
 *
 * Examples:
 *
 *     Arr\map_keys([0 => 1, 1 => 2, 2 => 3, 3 => 4, 4 => 5], fn($i) => $i * 2);
 *     => Arr(0 => 1, 2 => 2, 4 => 3, 6 => 4, 8 => 5)
 *
 * @template Tk1 of array-key
 * @template Tk2 of array-key
 * @template Tv
 *
 * @param iterable<Tk1, Tv> $iterable Iterable to be mapped over
 * @param (callable(Tk1): Tk2) $function
 *
 * @return array<Tk2, Tv>
 *
 * @deprecated use `Dict\map_keys` instead.
 * @see Dict\map()
 */
function map_keys(iterable $iterable, callable $function): array
{
    return Dict\map_keys($iterable, $function);
}
