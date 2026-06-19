<?php

declare(strict_types=1);

namespace Psl\Dict;

use function array_diff;
use function array_map;

/**
 * Computes the difference of iterables.
 *
 * @param iterable<Tk, Tv> $first
 * @param iterable<Tk, Tv> $second
 * @param iterable<Tk, Tv> ...$rest
 *
 * @return array<Tk, Tv>
 *
 * @api
 */
function diff<Tk : int|string = int|string, Tv = mixed>(iterable $first, iterable $second, iterable ...$rest): array
{
    return array_diff(
        namespace\from_iterable($first),
        namespace\from_iterable($second),
        ...array_map(namespace\from_iterable(...), $rest),
    );
}
