<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;

use function array_filter;
use function is_array;

/**
 * Returns a dict containing only the values for which the given predicate
 * returns `true`.
 *
 * The default predicate is casting the value to boolean.
 *
 * Example:
 *
 *      Dict\filter(['', '0', 'a', 'b'])
 *      => Dict(2 => 'a', 3 => 'b')
 *
 *      Dict\filter(['foo', 'bar', 'baz', 'qux'], fn(string $value): bool => Str\contains($value, 'a'));
 *      => Dict(1 => 'bar', 2 => 'baz')
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (Closure(Tv): bool)|null $predicate
 *
 * @return array<Tk, Tv>
 */
function filter(iterable $iterable, ?Closure $predicate = null): array
{
    /** @var (Closure(Tv): bool) $predicate */
    $predicate = $predicate ?? static fn(mixed $value): bool => (bool) $value;

    if (is_array($iterable)) {
        return array_filter(
            $iterable,
            /**
             * @param Tv $v
             */
            static fn(mixed $v): bool => $predicate($v)
        );
    }

    $result    = [];
    foreach ($iterable as $k => $v) {
        if ($predicate($v)) {
            $result[$k] = $v;
        }
    }

    return $result;
}
