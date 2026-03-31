<?php

declare(strict_types=1);

namespace Psl\Math;

use function min as php_min;

/**
 * Returns the smallest element of the given list, or null if the
 * list is empty.
 *
 * @template T of int|float
 *
 * @param list<T> $numbers
 *
 * @return ($numbers is non-empty-list<T> ? T : null)
 *
 * @pure
 *
 * @api
 */
function min(array $numbers): null|float|int
{
    if ([] === $numbers) {
        return null;
    }

    return php_min($numbers);
}
