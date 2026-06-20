<?php

declare(strict_types=1);

namespace Psl\Dict;

use function array_intersect;
use function array_map;

/**
 * Computes the intersection of iterables.
 *
 * @param iterable<Tk, Tv> $first
 * @param iterable<Tk, mixed> $second
 * @param iterable<Tk, mixed> ...$rest
 *
 * @return array<Tk, Tv>
 *
 * @api
 */
function intersect<Tk: string|int, Tv>(iterable $first, iterable $second, iterable ...$rest): array
{
    return array_intersect(
        namespace\from_iterable::<Tk, Tv>($first),
        namespace\from_iterable::<Tk, mixed>($second),
        ...array_map(namespace\from_iterable::<Tk, mixed>(...), $rest),
    );
}
