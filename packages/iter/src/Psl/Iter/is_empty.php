<?php

declare(strict_types=1);

namespace Psl\Iter;

use function is_array;

/**
 * Returns true if the given iterable is empty.
 *
 * @param iterable<T> $iterable
 *
 * @return ($iterable is non-empty-array|non-empty-list ? false : true)
 *
 * @psalm-assert-if-true empty $iterable
 *
 * @api
 */
function is_empty<T>(iterable $iterable): bool
{
    if (is_array($iterable)) {
        return [] === $iterable;
    }

    return 0 === namespace\count::<T>($iterable);
}
