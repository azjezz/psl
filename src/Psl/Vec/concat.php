<?php

declare(strict_types=1);

namespace Psl\Vec;

/**
 * Returns a new list formed by concatenating the given lists together.
 *
 * @template T
 *
 * @param list<T> $first
 * @param iterable<T> ...$rest
 *
 * @return list<T>
 */
function concat(array $first, iterable ...$rest): array
{
    $first = values($first);
    foreach ($rest as $arr) {
        foreach ($arr as $value) {
            $first[] = $value;
        }
    }

    return $first;
}
