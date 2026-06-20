<?php

declare(strict_types=1);

namespace Psl\Vec;

/**
 * Return all the unique values of an array, as a list.
 *
 * @param iterable<Tv> $iterable
 *
 * @return list<Tv>
 *
 * @api
 */
function unique<Tv>(iterable $iterable): array
{
    return namespace\unique_by::<mixed, mixed>(
        $iterable,
        /**
         * @pure
         */
        static fn(Tv $v): Tv => $v,
    );
}
