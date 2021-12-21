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
 * @return T|null
 */
function search(iterable $iterable, Closure $predicate)
{
    foreach ($iterable as $value) {
        if ($predicate($value)) {
            return $value;
        }
    }

    return null;
}
