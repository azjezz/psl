<?php

declare(strict_types=1);

namespace Psl\Dict;

use Psl\Iter;
use Psl\Vec;

use function array_diff;

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
    if (Iter\is_empty($first)) {
        return [];
    }

    return array_diff(from_iterable($first), from_iterable($second), ...Vec\map(
        $rest,
        /**
         * @param iterable<Tk, Tv> $iterable
         *
         * @return array<Tk, Tv>
         */
        static fn(iterable $iterable): array => from_iterable($iterable)
    ));
}
