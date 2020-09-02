<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Applies a mapping function to all keys of an array.
 *
 * The function is passed the current array key and should return a
 * modified array key.
 *
 * The value is left as-is and not passed to the mapping function.
 *
 * Examples:
 *
 *     Arr\map_keys([0 => 1, 1 => 2, 2 => 3, 3 => 4, 4 => 5], fn($i) => $i * 2);
 *     => Arr(0 => 1, 2 => 2, 4 => 3, 6 => 4, 8 => 5)
 *
 * @psalm-template Tk1 of array-key
 * @psalm-template Tk2 of array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk1, Tv>              $array    Array to be mapped over
 * @psalm-param (pure-callable(Tk1): Tk2)   $function
 *
 * @psalm-return array<Tk2, Tv>
 *
 * @psalm-pure
 */
function map_keys(array $array, callable $function): array
{
    $result = [];
    foreach ($array as $key => $value) {
        $result[$function($key)] = $value;
    }

    return $result;
}
