<?php

declare(strict_types=1);

namespace Psl\Iter;

use function in_array;
use function is_array;

/**
 * Returns true if the given iterable contains the value. Strict equality is
 * used.
 *
 * @param iterable<T> $iterable
 * @param T $value
 *
 * @api
 */
function contains<T>(iterable $iterable, T $value): bool
{
    if (is_array($iterable)) {
        return in_array($value, $iterable, true);
    }

    foreach ($iterable as $v) {
        if ($value === $v) {
            return true;
        }
    }

    return false;
}
