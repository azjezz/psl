<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;

/**
 * Maps each value of the iterable using the given function and filters out null results,
 * preserving the keys.
 *
 * This is equivalent to `Dict\filter_nulls(Dict\map($iterable, $function))`, but
 * more efficient as it performs both operations in a single pass.
 *
 * Examples:
 *
 *      Dict\map_nonnull(['a' => 1, 'b' => null, 'c' => 3], fn($v) => $v);
 *      => Dict('a' => 1, 'c' => 3)
 *
 *      Dict\map_nonnull([1, 2, 3], fn($v) => $v > 1 ? $v * 2 : null);
 *      => Dict(1 => 4, 2 => 6)
 *
 * @template Tk of array-key
 * @template Tv
 * @template T
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (Closure(Tv): (T|null)) $function
 *
 * @return array<Tk, T>
 */
function map_nonnull(iterable $iterable, Closure $function): array
{
    $result = [];
    foreach ($iterable as $key => $value) {
        $mapped = $function($value);
        if (null !== $mapped) {
            $result[$key] = $mapped;
        }
    }

    return $result;
}
