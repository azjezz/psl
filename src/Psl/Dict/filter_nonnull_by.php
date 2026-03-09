<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;

use function array_filter;
use function is_array;

/**
 * Filters values of the iterable, keeping only those for which the given function
 * returns a non-null value, preserving the keys.
 *
 * Unlike {@see map_nonnull()}, this function returns the original values, not the
 * mapped results.
 *
 * Example:
 *
 *      Dict\filter_nonnull_by(['a' => 'hello', 'b' => '', 'c' => 'world'], fn($v) => $v !== '' ? $v : null);
 *      => Dict('a' => 'hello', 'c' => 'world')
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (Closure(Tv): mixed) $function
 *
 * @return array<Tk, Tv>
 */
function filter_nonnull_by(iterable $iterable, Closure $function): array
{
    if (is_array($iterable)) {
        return array_filter($iterable, static fn($v) => null !== $function($v));
    }

    $result = [];
    foreach ($iterable as $key => $value) {
        if (null === $function($value)) {
            continue;
        }

        $result[$key] = $value;
    }

    return $result;
}
