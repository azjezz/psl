<?php

declare(strict_types=1);

namespace Psl\Dict;

use function array_unique;
use function is_array;

/**
 * Returns a new dict in which each value appears exactly once. Better performant than `Dict\unique()` when the values
 * are only scalars.
 *
 * @template Tk of array-key
 * @template Tv of scalar
 *
 * @param iterable<Tk, Tv> $iterable
 *
 * @return array<Tk, Tv>
 */
function unique_scalar(iterable $iterable): array
{
    if (is_array($iterable)) {
        return array_unique($iterable);
    }

    return unique_by(
        $iterable,
        /**
         * @param scalar $v
         *
         * @return scalar
         *
         * @pure
         */
        static fn($v) => $v
    );
}
