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
function unique<Tk: string|int, Tv>(iterable $iterable): array
{
    return namespace\unique_by::<Tk, Tv, Tv>(
        $iterable,
        /**
         * @pure
         */
        static fn(Tv $v): Tv => $v,
    );
}
