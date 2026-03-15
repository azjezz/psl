<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;

/**
 * Returns a dict where:
 *  - values are the result of calling `$valueFunc` on the original value/key
 *  - keys are the result of calling `$keyFunc` on the original value/key.
 *
 * Example:
 *
 *      Dict\pull_with_key(
 *          Vec\range(0, 10),
 *          fn($k, $v) => Str\chr($v + $k + 65),
 *          fn($k, $v) => 2**($v+$k)
 *      )
 *      => Dict(
 *          1 => 'A', 4 => 'C', 16 => 'E', 64 => 'G', 256 => 'I', 1024 => 'K',
 *          4096 => 'M', 16384 => 'O', 65536 => 'Q', 262144 => 'S', 1048576 => 'U'
 *      )
 *
 * @template Tk1
 * @template Tv1
 * @template Tk2 of array-key
 * @template Tv2
 *
 * @param iterable<Tk1, Tv1> $iterable
 * @param (Closure(Tk1, Tv1): Tv2) $valueFunc
 * @param (Closure(Tk1, Tv1): Tk2) $keyFunc
 *
 * @return array<Tk2, Tv2>
 */
function pull_with_key(iterable $iterable, Closure $valueFunc, Closure $keyFunc): array
{
    $result = [];
    foreach ($iterable as $key => $value) {
        $result[$keyFunc($key, $value)] = $valueFunc($key, $value);
    }

    return $result;
}
