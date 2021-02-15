<?php

declare(strict_types=1);

namespace Psl\Dict;

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
 *
 * @psalm-template Tk1
 * @psalm-template Tv1
 * @psalm-template Tk2 of array-key
 * @psalm-template Tv2
 *
 * @psalm-param iterable<Tk1, Tv1>    $iterable
 * @psalm-param (callable(Tv1): Tv2)  $value_func
 * @psalm-param (callable(Tv1): Tk2)  $key_func
 *
 * @psalm-return array<Tk2, Tv2>
 */
function pull(iterable $iterable, callable $value_func, callable $key_func): array
{
    $result = [];
    foreach ($iterable as $value) {
        $result[$key_func($value)] = $value_func($value);
    }

    return $result;
}
