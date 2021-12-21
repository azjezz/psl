<?php

declare(strict_types=1);

namespace Psl\Iter;

use Closure;

/**
 * Returns true if there is a value in the iterable that satisfies the
 * predicate.
 *
 * This function is short-circuiting, i.e. if the predicate matches for any one
 * element the remaining elements will not be considered anymore.
 *
 * @template  T
 *
 * @param iterable<T> $iterable Iterable to check against the predicate
 * @param (Closure(T): bool) $predicate
 */
function any(iterable $iterable, Closure $predicate): bool
{
    foreach ($iterable as $value) {
        if ($predicate($value)) {
            return true;
        }
    }

    return false;
}
