<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Vec;

/**
 * Returns a new array formed by concatenating the given arrays together.
 *
 * @template T
 *
 * @param list<T> $first
 * @param iterable<T> ...$rest
 *
 * @return list<T>
 *
 * @deprecated since 1.2, use Vec\concat instead.
 * @see Vec\concat()
 */
function concat(array $first, iterable ...$rest): array
{
    return Vec\concat($first, ...$rest);
}
