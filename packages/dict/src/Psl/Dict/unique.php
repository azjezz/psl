<?php

declare(strict_types=1);

namespace Psl\Dict;

/**
 * Returns a new dict in which each value appears exactly once.
 *
 * @param iterable<Tk, Tv> $iterable
 *
 * @return array<Tk, Tv>
 *
 * @api
 */
function unique<Tk : int|string = int|string, Tv = mixed>(iterable $iterable): array
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
