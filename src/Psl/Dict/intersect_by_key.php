<?php

declare(strict_types=1);

namespace Psl\Dict;

use Psl\Iter;
use Psl\Vec;

use function array_intersect_key;

/**
 * Computes the intersection of iterables using keys for comparison.
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
function intersect_by_key(iterable $first, iterable $second, iterable ...$rest): array
{
    if (Iter\is_empty($first)) {
        return [];
    }

    return array_intersect_key(from_iterable($first), from_iterable($second), ...Vec\map(
        $rest,
        /**
         * @param iterable<Tk, Tv> $iterable
         *
         * @return array<Tk, Tv>
         */
        static fn(iterable $iterable): array => from_iterable($iterable)
    ));
}
