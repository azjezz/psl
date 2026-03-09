<?php

declare(strict_types=1);

namespace Psl\Dict;

use Psl\Iter;

use function array_intersect;
use function array_map;

/**
 * Computes the intersection of iterables.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv> $first
 * @param iterable<Tk, mixed> $second
 * @param iterable<Tk, mixed> ...$rest
 *
 * @return array<Tk, Tv>
 */
function intersect(iterable $first, iterable $second, iterable ...$rest): array
{
    if (Iter\is_empty($first)) {
        return [];
    }

    return array_intersect(from_iterable($first), from_iterable($second), ...array_map(from_iterable(...), $rest));
}
