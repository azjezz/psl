<?php

declare(strict_types=1);

namespace Psl\Vec;

use function array_unique;
use function is_array;

/**
 * Returns a new list in which each value appears exactly once. Better performant than `Vec\unique()` when the values
 * are only scalars.
 *
 * @template Tv of scalar
 *
 * @param iterable<Tv> $iterable
 *
 * @return list<Tv>
 */
function unique_scalar(iterable $iterable): array
{
    if (is_array($iterable)) {
        return namespace\values(array_unique($iterable));
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
        static fn(mixed $v): mixed => $v,
    );
}
