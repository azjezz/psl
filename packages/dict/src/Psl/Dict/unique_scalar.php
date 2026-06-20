<?php

declare(strict_types=1);

namespace Psl\Dict;

use function array_unique;
use function is_array;

/**
 * Returns a new dict in which each value appears exactly once. Better performant than `Dict\unique()` when the values
 * are only scalars.
 *
 * @param iterable<Tk, Tv> $iterable
 *
 * @return array<Tk, Tv>
 *
 * @api
 */
function unique_scalar<Tk: string|int, Tv: int|float|string|bool>(iterable $iterable): array
{
    if (is_array($iterable)) {
        return array_unique($iterable);
    }

    return namespace\unique_by::<Tk, Tv, Tv>(
        $iterable,
        /**
         * @param scalar $v
         *
         * @return scalar
         *
         * @pure
         */
        static fn(mixed $v): mixed => $v,
    );
}
