<?php

declare(strict_types=1);

namespace Psl\Vec;

use Closure;

use function array_map;
use function array_values;
use function is_array;

/**
 * Applies a mapping function to all values of an iterable.
 *
 * The function is passed the current iterable value and should return a
 * modified value.
 *
 * Examples:
 *
 *     Vec\map([1, 2, 3, 4, 5], fn($i) => $i * 2);
 *     => Vec(2, 4, 6, 8, 10)
 *
 * @template Tk
 * @template Tv
 * @template T
 *
 * @param iterable<Tk, Tv> $iterable Iterable to be mapped over
 * @param (Closure(Tv): T) $function
 *
 * @return ($iterable is non-empty-array ? non-empty-list<T> : list<T>)
 */
function map(iterable $iterable, Closure $function): array
{
    if (is_array($iterable)) {
        return array_values(array_map(
            /**
             * @param Tv $v
             */
            static fn($v) => $function($v),
            $iterable
        ));
    }

    $result = [];
    foreach ($iterable as $value) {
        $result[] = $function($value);
    }

    return $result;
}
