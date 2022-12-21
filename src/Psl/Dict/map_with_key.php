<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;

/**
 * Applies a mapping function to all values of an iterable.
 *
 * The function is passed the current iterable key and value and should return a
 * modified value.
 *
 * The key is left as-is.
 *
 * Examples:
 *
 *     Dict\map_with_key([1, 2, 3, 4, 5], fn($k, $v) => $k + $v);
 *     => Dict(1, 3, 5, 7, 9)
 *
 * @template Tk of array-key
 * @template Tv
 * @template T
 *
 * @param iterable<Tk, Tv> $iterable Iterable to be mapped over
 * @param (Closure(Tk,Tv): T) $function
 *
 * @return ($iterable is non-empty-array ? non-empty-array<Tk, T> : array<Tk, T>)
 */
function map_with_key(iterable $iterable, Closure $function): array
{
    $result = [];
    foreach ($iterable as $key => $value) {
        $result[$key] = $function($key, $value);
    }

    return $result;
}
