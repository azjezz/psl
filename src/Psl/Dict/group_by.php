<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;

/**
 * Returns a new array where
 *  - keys are the results of the given function called on the given values.
 *  - values are an array of original values that all produced the same key.
 *
 * If a value produces a null key, it's omitted from the result.
 *
 * Example:
 *
 *      Dict\group_by(
 *          [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
 *          fn($i) => $i < 2 ? null : $i + 5
 *      )
 *      => Dict(
 *          7 => [2], 8 => [3], 9 => [4], 10 => [5], 11 => [6], 12 => [7], 13 => [8], 14 => [9], 15 => [10]
 *      )
 *
 *      Dict\group_by(
 *          [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
 *          fn($i) => $i < 2 ? null : ($i >= 7 ? 12 : $i +5)
 *      )
 *      => Dict(7 => [2], 8 => [3], 9 => [4], 10 => [5], 11 => [6], 12 => [7, 8, 9, 10])
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tv> $values
 * @param (Closure(Tv): ?Tk) $keyFunc
 *
 * @return array<Tk, list<Tv>>
 */
function group_by(iterable $values, Closure $keyFunc): array
{
    $result = [];
    foreach ($values as $value) {
        $key = $keyFunc($value);
        if (null === $key) {
            continue;
        }

        /** @var Tk $key */
        $result[$key] ??= [];
        $result[$key][] = $value;
    }

    return $result;
}
