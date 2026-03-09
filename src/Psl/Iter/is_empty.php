<?php

declare(strict_types=1);

namespace Psl\Iter;

use function is_array;

/**
 * Returns true if the given iterable is empty.
 *
 * @template T
 *
 * @param iterable<T> $iterable
 *
 * @return ($iterable is non-empty-array|non-empty-list ? false : true)
 *
 * @psalm-assert-if-true empty $iterable
 */
function is_empty(iterable $iterable): bool
{
    if (is_array($iterable)) {
        return [] === $iterable;
    }

    return 0 === namespace\count($iterable);
}
