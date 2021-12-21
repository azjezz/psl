<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;

use function array_filter;
use function is_array;

use const ARRAY_FILTER_USE_KEY;

/**
 * Returns a dict containing only the keys for which the given predicate
 * returns `true`.
 *
 * The default predicate is casting the key to boolean.
 *
 * Example:
 *
 *      Dict\filter_keys([0 => 'a', 1 => 'b'])
 *      => Dict(1 => 'b')
 *
 *      Dict\filter_keys([0 => 'a', 1 => 'b', 2 => 'c'], fn(int $key): bool => $key <= 1);
 *      => Dict(0 => 'a', 1 => 'b')
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (Closure(Tk): bool)|null $predicate
 *
 * @return array<Tk, Tv>
 */
function filter_keys(iterable $iterable, ?Closure $predicate = null): array
{
    /** @var (Closure(Tk): bool) $predicate */
    $predicate = $predicate ?? static fn(mixed $value): bool => (bool) $value;

    if (is_array($iterable)) {
        return array_filter(
            $iterable,
            /**
             * @param Tk $k
             */
            static fn($k): bool => $predicate($k),
            ARRAY_FILTER_USE_KEY
        );
    }

    $result    = [];
    foreach ($iterable as $k => $v) {
        if ($predicate($k)) {
            $result[$k] = $v;
        }
    }

    return $result;
}
