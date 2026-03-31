<?php

declare(strict_types=1);

namespace Psl\Math;

use function max as php_max;

/**
 * Returns the largest element of the given list, or null if the
 * list is empty.
 *
 * @template T of int|float
 *
 * @param list<T> $numbers
 *
 * @return ($numbers is non-empty-list<T> ? T : T|null)
 *
 * @pure
 *
 * @api
 */
function max(array $numbers): null|int|float
{
    if ([] === $numbers) {
        return null;
    }

    return php_max($numbers);
}
