<?php

declare(strict_types=1);

namespace Psl\Vec;

use Closure;

use function array_filter;
use function array_values;
use function is_array;

/**
 * Filters values of the iterable, keeping only those for which the given function
 * returns a non-null value.
 *
 * Unlike {@see map_nonnull()}, this function returns the original values, not the
 * mapped results.
 *
 * Example:
 *
 *      Vec\filter_nonnull_by(['hello', '', 'world'], fn($v) => $v !== '' ? $v : null);
 *      => Vec('hello', 'world')
 *
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (Closure(Tv): mixed) $function
 *
 * @return list<Tv>
 */
function filter_nonnull_by(iterable $iterable, Closure $function): array
{
    if (is_array($iterable)) {
        return array_values(array_filter($iterable, static fn($v) => null !== $function($v)));
    }

    $result = [];
    foreach ($iterable as $value) {
        if (null === $function($value)) {
            continue;
        }

        $result[] = $value;
    }

    return $result;
}
