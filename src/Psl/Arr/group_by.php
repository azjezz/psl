<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl;
use Psl\Str;
use Psl\Type;

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
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param list<Tv>                    $values
 * @psalm-param (pure-callable(Tv): ?Tk)    $key_func
 *
 * @psalm-return array<Tk, list<Tv>>
 *
 * @psalm-pure
 */
function group_by(array $values, callable $key_func): array
{
    $result = [];
    foreach ($values as $value) {
        $key = $key_func($value);
        if (null === $key) {
            continue;
        }

        Psl\invariant(Type\is_arraykey($key), 'Expected $key_func to return a value of type array-key, value of type (%s) returned.', gettype($key));
        /** @psalm-var Tk $key */
        $result[$key] = $result[$key] ?? [];
        $result[$key][] = $value;
    }

    return $result;
}
