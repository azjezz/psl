<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Returns a new array where
 *  - keys are the results of the given function called on the given values.
 *  - values are an array of original values that all produced the same key.
 *
 * If a value produces a null key, it's omitted from the result.
 *
 * Example:
 *
 *      Arr\group_by(Iter\range(0, 10), fn($i) => $i < 2 ? null : $i + 5)
 *      => Arr(
 *          7 => [2], 8 => [3], 9 => [4], 10 => [5], 11 => [6], 12 => [7], 13 => [8], 14 => [9], 15 => [10]
 *      )
 *
 *      Arr\group_by(
 *          Iter\range(0, 10),
 *          fn($i) => $i < 2 ? null : ($i >= 7 ? 12 : $i +5)
 *      )
 *      => Arr(7 => [2], 8 => [3], 9 => [4], 10 => [5], 11 => [6], 12 => [7, 8, 9, 10])
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tv>        $values
 * @psalm-param (callable(Tv): ?Tk) $key_func
 *
 * @psalm-return array<Tk, array<Tv>>
 */
function group_by(iterable $values, callable $key_func): array
{
    $result = [];
    foreach ($values as $value) {
        $key = $key_func($value);
        if (null === $key) {
            continue;
        }

        $result[$key] = $result[$key] ?? [];
        $result[$key][] = $value;
    }

    return $result;
}
