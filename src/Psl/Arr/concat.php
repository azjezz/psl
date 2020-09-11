<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Returns a new array formed by concatenating the given arrays together.
 *
 * @psalm-template T
 *
 * @psalm-param list<T>     $first
 * @psalm-param iterable<T> ...$rest
 *
 * @psalm-return list<T>
 */
function concat(array $first, iterable ...$rest): array
{
    /** @psalm-var list<T> $first */
    $first = values($first);

    foreach ($rest as $arr) {
        foreach ($arr as $value) {
            $first[] = $value;
        }
    }

    return $first;
}
