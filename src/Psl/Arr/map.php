<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Applies a mapping function to all values of an array.
 *
 * The function is passed the current array value and should return a
 * modified array value.
 *
 * The key is left as-is and not passed to the mapping function.
 *
 * Examples:
 *
 *     Arr\map([1, 2, 3, 4, 5], fn($i) => $i * 2);
 *     => Arr(2, 4, 6, 8, 10)
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 * @psalm-template T
 *
 * @psalm-param array<Tk, Tv>           $array      Array to be mapped over
 * @psalm-param (pure-callable(Tv): T)  $function
 *
 * @psalm-return array<Tk, T>
 *
 * @psalm-pure
 */
function map(array $array, callable $function): array
{
    $result = [];
    foreach ($array as $key => $value) {
        $result[$key] = $function($value);
    }

    return $result;
}
