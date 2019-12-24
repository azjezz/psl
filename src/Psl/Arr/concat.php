<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Iter;

/**
 * Returns a new array formed by concatenating the given iterables together.
 *
 * @psalm-template T
 *
 * @psalm-param iterable<T> $first
 * @psalm-param iterable<T> ...$rest
 *
 * @psalm-return array<int, T>
 *
 * @psalm-suppress InvalidReturnType
 */
function concat(iterable $first, iterable ...$rest): array
{
    /** @psalm-var array<int, T> $result */
    $result = Iter\to_array($first);
    foreach ($rest as $iterable) {
        foreach ($iterable as $value) {
            $result[] = $value;
        }
    }

    return $result;
}
