<?php

declare(strict_types=1);

namespace Psl\Vec;

use function array_unique;
use function array_values;
use function is_array;

/**
 * Returns a new list in which each value appears exactly once. Better performant than `Vec\unique()` when the values
 * are only scalars.
 *
 * @param iterable<Tv> $iterable
 *
 * @return list<Tv>
 *
 * @api
 */
function unique_scalar<Tv: int|float|string|bool>(iterable $iterable): array
{
    if (is_array($iterable)) {
        return array_values(array_unique($iterable));
    }

    return namespace\unique_by::<mixed, int|float|string|bool>(
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
