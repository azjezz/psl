<?php

declare(strict_types=1);

namespace Psl\Iter;

use Closure;
use Psl\Option\Option;

/**
 * Searches an iterable until a predicate returns true, then returns
 * the value of the matching element wrapped in {@see Option::some}.
 * If a predicate never returns true, {@see Option::none} will be returned.
 *
 * Examples:
 *
 *      Iter\search_with_keys_opt(['foo', 'bar', 'baz'], fn($k, $v) => 'baz' === $v)
 *      => Option::some('baz')
 *
 *      Iter\search_with_keys_opt(['foo', 'bar', 'baz'], fn($k, $v) => 'qux' === $v)
 *      => Option::none()
 *
 * @template TKey
 * @template TValue
 *
 * @param iterable<TKey, TValue> $iterable The iterable to search
 * @param (Closure(TKey, TValue): bool) $predicate
 *
 * @return Option<TValue>
 */
function search_with_keys_opt(iterable $iterable, Closure $predicate): Option
{
    foreach ($iterable as $key => $value) {
        if ($predicate($key, $value)) {
            return Option::some($value);
        }
    }

    return Option::none();
}
