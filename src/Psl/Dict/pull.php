<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;

/**
 * Returns a dict where:
 *  - values are the result of calling `$value_func` on the original value
 *  - keys are the result of calling `$key_func` on the original value.
 *
 * Example:
 *
 *      Dict\pull(
 *          Vec\range(0, 10),
 *          fn($i) => Str\chr($i + 65),
 *          fn($i) => 2**$i,
 *      )
 *      => Dict(
 *          1 => 'A', 2 => 'B', 4 => 'C', 8 => 'D', 16 => 'E', 32 => 'F',
 *          64 => 'G', 128 => 'H', 256 => 'I', 512 => 'J', 1024 => 'K'
 *      )
 *
 * @template Tk1
 * @template Tv1
 * @template Tk2 of array-key
 * @template Tv2
 *
 * @param iterable<Tk1, Tv1> $iterable
 * @param (Closure(Tv1): Tv2) $value_func
 * @param (Closure(Tv1): Tk2) $key_func
 *
 * @return array<Tk2, Tv2>
 */
function pull(iterable $iterable, Closure $value_func, Closure $key_func): array
{
    $result = [];
    foreach ($iterable as $value) {
        $result[$key_func($value)] = $value_func($value);
    }

    return $result;
}
