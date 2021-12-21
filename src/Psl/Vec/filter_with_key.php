<?php

declare(strict_types=1);

namespace Psl\Vec;

use Closure;

use function array_filter;
use function array_values;
use function is_array;

use const ARRAY_FILTER_USE_BOTH;

/**
 * Returns a vec containing only the values for which the given predicate
 * returns `true`.
 *
 * The default predicate is casting the value to boolean.
 *
 * Example:
 *
 *      Vec\filter_with_key(['a', '0', 'b', 'c'])
 *      => Vec('b', 'c')
 *
 *      Vec\filter_with_key(
 *          ['foo', 'bar', 'baz', 'qux'],
 *          fn(int $key, string $value): bool => $key > 1 && Str\contains($value, 'a')
 *      );
 *      => Vec('baz')
 *
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (Closure(Tk, Tv): bool)|null $predicate
 *
 * @return list<Tv>
 */
function filter_with_key(iterable $iterable, ?Closure $predicate = null): array
{
    $predicate = $predicate ??
        /**
         * @param Tk $_k
         * @param Tv $v
         */
        static fn (mixed $_k, mixed $v): bool => (bool) $v;

    if (is_array($iterable)) {
        return array_values(array_filter(
            $iterable,
            /**
             * @param Tv $v
             * @param Tk $k
             */
            static fn($v, $k): bool => $predicate($k, $v),
            ARRAY_FILTER_USE_BOTH
        ));
    }

    $result = [];
    foreach ($iterable as $k => $v) {
        if ($predicate($k, $v)) {
            $result[] = $v;
        }
    }

    return $result;
}
