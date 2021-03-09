<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Dict;

/**
 * Returns a new array where
 *  - keys are the results of the given function called on the given values.
 *  - values are an array of original values that all produced the same key.
 *
 * If a value produces a null key, it's omitted from the result.
 *
 * Example:
 *
 *      Arr\group_by(
 *          [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
 *          fn($i) => $i < 2 ? null : $i + 5
 *      )
 *      => Arr(
 *          7 => [2], 8 => [3], 9 => [4], 10 => [5], 11 => [6], 12 => [7], 13 => [8], 14 => [9], 15 => [10]
 *      )
 *
 *      Arr\group_by(
 *          [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
 *          fn($i) => $i < 2 ? null : ($i >= 7 ? 12 : $i +5)
 *      )
 *      => Arr(7 => [2], 8 => [3], 9 => [4], 10 => [5], 11 => [6], 12 => [7, 8, 9, 10])
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tv> $values
 * @param (callable(Tv): ?Tk) $key_func
 *
 * @return array<Tk, list<Tv>>
 *
 * @deprecated use `Dict\group_by` instead.
 * @see Dict\group_by()
 */
function group_by(iterable $values, callable $key_func): array
{
    return Dict\group_by($values, $key_func);
}
