<?php

declare(strict_types=1);

namespace Psl\Vec;

use Closure;

use function array_filter;
use function array_values;
use function is_array;

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
 * @template T
 *
 * @param iterable<T> $iterable
 * @param (Closure(T): bool)|null $predicate
 *
 * @return list<T>
 */
function filter(iterable $iterable, ?Closure $predicate = null): array
{
    /** @var (Closure(T): bool) $predicate */
    $predicate = $predicate ?? static fn(mixed $value): bool => (bool) $value;
    if (is_array($iterable)) {
        return array_values(array_filter(
            $iterable,
            /**
             * @param T $t
             */
            static fn(mixed $t): bool => $predicate($t)
        ));
    }

    $result    = [];
    foreach ($iterable as $v) {
        if ($predicate($v)) {
            $result[] = $v;
        }
    }

    return $result;
}
