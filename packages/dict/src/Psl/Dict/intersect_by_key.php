<?php

declare(strict_types=1);

namespace Psl\Dict;

use function array_intersect_key;
use function array_map;

/**
 * Computes the intersection of iterables using keys for comparison.
 *
 * @param iterable<Tk, Tv> $first
 * @param iterable<Tk, mixed> $second
 * @param iterable<Tk, mixed> ...$rest
 *
 * @return array<Tk, Tv>
 *
 * @api
 */
function intersect_by_key<Tk : int|string = int|string, Tv = mixed>(iterable $first, iterable $second, iterable ...$rest): array
{
    return array_intersect_key(
        namespace\from_iterable($first),
        namespace\from_iterable($second),
        ...array_map(namespace\from_iterable(...), $rest),
    );
}
