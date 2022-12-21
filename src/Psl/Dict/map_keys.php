<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;

/**
 * Applies a mapping function to all keys of an iterable.
 *
 * The function is passed the current iterable key and should return a
 * modified key.
 *
 * The value is left as-is and not passed to the mapping function.
 *
 * Examples:
 *
 *     Dict\map_keys([0 => 1, 1 => 2, 2 => 3, 3 => 4, 4 => 5], fn($i) => $i * 2);
 *     => Dict(0 => 1, 2 => 2, 4 => 3, 6 => 4, 8 => 5)
 *
 * @template Tk1 of array-key
 * @template Tk2 of array-key
 * @template Tv
 *
 * @param iterable<Tk1, Tv> $iterable Iterable to be mapped over
 * @param (Closure(Tk1): Tk2) $function
 *
 * @return ($iterable is non-empty-array ? non-empty-array<Tk2, Tv> : array<Tk2, Tv>)
 */
function map_keys(iterable $iterable, Closure $function): array
{
    $result = [];
    foreach ($iterable as $key => $value) {
        $result[$function($key)] = $value;
    }

    return $result;
}
