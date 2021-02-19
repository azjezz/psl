<?php

declare(strict_types=1);

namespace Psl\Vec;

use Closure;

/**
 * Returns a vec containing only the values for which the given predicate
 * returns `true`.
 *
 * The default predicate is casting the value to boolean.
 *
 * Example:
 *
 *      Vec\filter(['', '0', 'a', 'b'])
 *      => Vec('a', 'b')
 *
 *      Vec\filter(['foo', 'bar', 'baz', 'qux'], fn(string $value): bool => Str\contains($value, 'a'));
 *      => Vec('bar', 'baz')
 *
 * @psalm-template T
 *
 * @psalm-param iterable<T>                 $iterable
 * @psalm-param (callable(T): bool)|null    $predicate
 *
 * @psalm-return list<T>
 */
function filter(iterable $iterable, ?callable $predicate = null): array
{
    /** @psalm-var (callable(T): bool) $predicate */
    $predicate = $predicate ?? Closure::fromCallable('Psl\Internal\boolean');
    $result    = [];
    foreach ($iterable as $v) {
        if ($predicate($v)) {
            $result[] = $v;
        }
    }

    return $result;
}
