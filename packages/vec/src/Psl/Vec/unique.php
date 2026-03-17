<?php

declare(strict_types=1);

namespace Psl\Vec;

/**
 * Return all the unique values of an array, as a list.
 *
 * @template Tv
 *
 * @param iterable<Tv> $iterable
 *
 * @return list<Tv>
 */
function unique(iterable $iterable): array
{
    return namespace\unique_by(
        $iterable,
        /**
         * @param Tv $v
         *
         * @return Tv
         *
         * @pure
         */
        static fn(mixed $v): mixed => $v,
    );
}
