<?php

declare(strict_types=1);

namespace Psl\Vec;

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
 * @param iterable<Tk, Tv>   $iterable Iterable to be mapped over
 * @param (callable(Tv): T)  $function
 *
 * @return list<T>
 */
function map(iterable $iterable, callable $function): array
{
    $result = [];
    foreach ($iterable as $value) {
        $result[] = $function($value);
    }

    return $result;
}
