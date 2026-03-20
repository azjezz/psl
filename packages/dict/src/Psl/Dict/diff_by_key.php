<?php

declare(strict_types=1);

namespace Psl\Dict;

use function array_diff_key;
use function array_map;

/**
 * Computes the difference of iterables using keys for comparison.
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
function diff_by_key(iterable $first, iterable $second, iterable ...$rest): array
{
    return array_diff_key(
        namespace\from_iterable($first),
        namespace\from_iterable($second),
        ...array_map(namespace\from_iterable(...), $rest),
    );
}
