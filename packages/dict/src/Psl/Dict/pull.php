<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;

/**
 * Returns a dict where:
 *  - values are the result of calling `$valueFunc` on the original value
 *  - keys are the result of calling `$keyFunc` on the original value.
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
 * @param (Closure(Tv1): Tv2) $valueFunc
 * @param (Closure(Tv1): Tk2) $keyFunc
 *
 * @return array<Tk2, Tv2>
 */
function pull(iterable $iterable, Closure $valueFunc, Closure $keyFunc): array
{
    $result = [];
    foreach ($iterable as $value) {
        $result[$keyFunc($value)] = $valueFunc($value);
    }

    return $result;
}
