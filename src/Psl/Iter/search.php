<?php

declare(strict_types=1);

namespace Psl\Iter;

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
 * @psalm-template T
 *
 * @psalm-param iterable<T>         $iterable  The iterable to search
 * @psalm-param (callable(T): bool) $predicate
 *
 * @psalm-return null|T
 */
function search(iterable $iterable, callable $predicate)
{
    foreach ($iterable as $value) {
        if ($predicate($value)) {
            return $value;
        }
    }

    return null;
}
