<?php

declare(strict_types=1);

namespace Psl\Dict;

use function array_diff;
use function array_map;

/**
 * Computes the difference of iterables.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv> $first
 * @param iterable<Tk, Tv> $second
 * @param iterable<Tk, Tv> ...$rest
 *
 * @return array<Tk, Tv>
 */
function diff(iterable $first, iterable $second, iterable ...$rest): array
{
    return array_diff(
        namespace\from_iterable($first),
        namespace\from_iterable($second),
        ...array_map(namespace\from_iterable(...), $rest),
    );
}
