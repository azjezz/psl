<?php

declare(strict_types=1);

namespace Psl\Vec;

use Closure;

/**
 * Maps each value of the iterable using the given function and filters out null results.
 *
 * This is equivalent to `Vec\filter_nulls(Vec\map($iterable, $function))`, but
 * more efficient as it performs both operations in a single pass.
 *
 * Examples:
 *
 *      Vec\map_nonnull(['a', null, 'b'], fn($v) => $v);
 *      => Vec('a', 'b')
 *
 *      Vec\map_nonnull([1, 2, 3], fn($v) => $v > 1 ? $v * 2 : null);
 *      => Vec(4, 6)
 *
 * @template Tk
 * @template Tv
 * @template T
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (Closure(Tv): (T|null)) $function
 *
 * @return list<T>
 */
function map_nonnull(iterable $iterable, Closure $function): array
{
    $result = [];
    foreach ($iterable as $value) {
        $mapped = $function($value);
        if (null !== $mapped) {
            $result[] = $mapped;
        }
    }

    return $result;
}
