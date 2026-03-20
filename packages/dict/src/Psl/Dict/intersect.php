<?php

declare(strict_types=1);

namespace Psl\Dict;

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
    return array_intersect(
        namespace\from_iterable($first),
        namespace\from_iterable($second),
        ...array_map(namespace\from_iterable(...), $rest),
    );
}
