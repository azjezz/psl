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
 *      Iter\search(['foo', 'bar', 'baz'], fn($v) => 'baz' === $v)
 *      => Str('baz')
 *
 *      Iter\search(['foo', 'bar', 'baz'], fn($v) => 'qux' === $v)
 *      => Null
 *
 * @template T
 *
 * @param iterable<T> $iterable The iterable to search
 * @param (Closure(T): bool) $predicate
 *
 * @return Option<T>
 */
function search_opt(iterable $iterable, Closure $predicate): Option
{
    foreach ($iterable as $value) {
        if ($predicate($value)) {
            return Option::some($value);
        }
    }

    return Option::none();
}
