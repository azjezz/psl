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
 * @param (callable(T): bool)|null $predicate
 *
 * @return list<T>
 */
function filter(iterable $iterable, ?callable $predicate = null): array
{
    /** @var (callable(T): bool) $predicate */
    $predicate = $predicate ?? Closure::fromCallable('Psl\Internal\boolean');
    if (is_array($iterable)) {
        return array_values(array_filter(
            $iterable,
            /**
             * @param T $t
             */
            static fn($t): bool => $predicate($t)
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
