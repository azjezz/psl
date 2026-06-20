<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;

use function array_filter;
use function is_array;

use const ARRAY_FILTER_USE_BOTH;

/**
 * Returns a dict containing only the keys and values for which the given predicate
 * returns `true`.
 *
 * The default predicate is casting both they key and the value to boolean.
 *
 * Example:
 *
 *      Dict\filter_with_key(['a', '0', 'b', 'c'])
 *      => Dict(2 => 'b', 3 => 'c')
 *
 *      Dict\filter_with_key(
 *          ['foo', 'bar', 'baz', 'qux'],
 *          fn(int $key, string $value): bool => $key > 1 && Str\contains($value, 'a')
 *      );
 *      => Dict(2 => 'baz')
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (Closure(Tk, Tv): bool)|null $predicate
 *
 * @return array<Tk, Tv>
 *
 * @api
 */
function filter_with_key<Tk: string|int, Tv>(iterable $iterable, null|Closure $predicate = null): array
{
    $predicate ??= static fn(Tk $_, Tv $v): bool => (bool) $v;

    if (is_array($iterable)) {
        return array_filter(
            $iterable,
            static fn(Tv $v, Tk $k): bool => $predicate($k, $v),
            ARRAY_FILTER_USE_BOTH,
        );
    }

    /** @var array<Tk, Tv> $result */
    $result = [];
    foreach ($iterable as $k => $v) {
        if (!$predicate($k, $v)) {
            continue;
        }

        $result[$k] = $v;
    }

    return $result;
}
