<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Iter;

/**
 * Merges multiple iterables into a new array. In the case of duplicate
 * keys, later values will overwrite the previous ones.
 *
 * Example:
 *      Arr\merge([1, 2], [9, 8])
 *      => Arr(0 => 9, 1 => 8)
 *
 *      Arr\merge([0 => 1, 1 => 2], [2 => 9, 3 => 8])
 *      => Arr(0 => 1, 1 => 2, 2 => 9, 3 => 8)
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv> $first
 * @psalm-param iterable<Tk, Tv> ...$rest
 *
 * @psalm-return array<Tk, Tv>
 *
 * @psalm-suppress InvalidReturnType
 * @psalm-suppress InvalidReturnStatement
 */
function merge(iterable $first, iterable ...$rest): array
{
    $result = is_array($first) ? $first : Iter\to_array_with_keys($first);
    foreach ($rest as $iterable) {
        foreach ($iterable as $k => $v) {
            $result[$k] = $v;
        }
    }

    return $result;
}
