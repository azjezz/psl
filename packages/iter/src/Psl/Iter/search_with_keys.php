<?php

declare(strict_types=1);

namespace Psl\Iter;

use Closure;

/**
 * Searches an iterable until a predicate returns true, then returns
 * the value of the matching element.
 *
 * Examples:
 *
 *      Iter\search_with_keys(['foo', 'bar', 'baz'], fn($k, $v) => 'baz' === $v)
 *      => 'baz'
 *
 *      Iter\search_with_keys(['foo', 'bar', 'baz'], fn($k, $v) => 'qux' === $v)
 *      => null
 *
 * @param iterable<TKey, TValue> $iterable The iterable to search
 * @param (Closure(TKey, TValue): bool) $predicate
 *
 * @return TValue|null
 *
 * @api
 */
function search_with_keys<TKey, TValue>(iterable $iterable, Closure $predicate): TValue|null
{
    foreach ($iterable as $key => $value) {
        if (!$predicate($key, $value)) {
            continue;
        }

        return $value;
    }

    return null;
}
