<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Returns true if there is a value in the iterable that satisfies the
 * predicate.
 *
 * This function is short-circuiting, i.e. if the predicate matches for any one
 * element the remaining elements will not be considered anymore.
 *
 * Examples:
 *
 *      Iter\any(Iter\range(1, 10), fn($i) => $i > 5)
 *      => Bool(true)
 *      Iter\any(Iter\range(-8, 4), fn($i) => $i > 5)
 *      => Bool(false)
 *
 * @template  T
 *
 * @param iterable<T> $iterable Iterable to check against the predicate
 * @param (callable(T): bool) $predicate
 *
 * @return bool Whether the predicate matches any value
 */
function any(iterable $iterable, callable $predicate): bool
{
    foreach ($iterable as $value) {
        if ($predicate($value)) {
            return true;
        }
    }

    return false;
}
