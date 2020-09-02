<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Applies a mapping function to all values of an array.
 *
 * The function is passed the current array key and value and should return a
 * modified array value.
 *
 * The key is left as-is.
 *
 * Examples:
 *
 *     Arr\map_with_key([1, 2, 3, 4, 5], fn($k, $v) => $k + $v);
 *     => Arr(1, 3, 5, 7, 9)
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 * @psalm-template T
 *
 * @psalm-param array<Tk, Tv>               $array    Array to be mapped over
 * @psalm-param (pure-callable(Tk,Tv): T)   $function
 *
 * @psalm-return array<Tk, T>
 *
 * @psalm-pure
 */
function map_with_key(array $array, callable $function): array
{
    $result = [];
    foreach ($array as $key => $value) {
        $result[$key] = $function($key, $value);
    }

    return $result;
}
