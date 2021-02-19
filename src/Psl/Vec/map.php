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
 * @psalm-template Tk
 * @psalm-template Tv
 * @psalm-template T
 *
 * @psalm-param iterable<Tk, Tv>   $iterable Iterable to be mapped over
 * @psalm-param (callable(Tv): T)  $function
 *
 * @psalm-return list<T>
 */
function map(iterable $iterable, callable $function): array
{
    $result = [];
    foreach ($iterable as $value) {
        $result[] = $function($value);
    }

    return $result;
}
