<?php

declare(strict_types=1);

namespace Psl\Vec;

use Closure;

use function array_filter;
use function array_values;
use function is_array;

use const ARRAY_FILTER_USE_KEY;

/**
 * Returns a vec containing only the values for which the given predicate
 * returns `true`.
 *
 * The default predicate is casting the key to boolean.
 *
 * Example:
 *
 *      Vec\filter_keys([0 => 'a', 1 => 'b'])
 *      => Vec('b')
 *
 *      Vec\filter_keys([0 => 'a', 1 => 'b', 2 => 'c'], fn(int $key): bool => $key <= 1);
 *      => Vec('a', 'b')
 *
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (Closure(Tk): bool)|null $predicate
 *
 * @return list<Tv>
 */
function filter_keys(iterable $iterable, ?Closure $predicate = null): array
{
    /** @var (Closure(Tk): bool) $predicate */
    $predicate = $predicate ?? static fn(mixed $value): bool => (bool) $value;
    if (is_array($iterable)) {
        return array_values(array_filter(
            $iterable,
            /**
             * @param Tk $t
             */
            static fn($t): bool => $predicate($t),
            ARRAY_FILTER_USE_KEY
        ));
    }

    $result    = [];
    foreach ($iterable as $k => $v) {
        if ($predicate($k)) {
            $result[] = $v;
        }
    }

    return $result;
}
