<?php

declare(strict_types=1);

namespace Psl\Dict;

/**
 * Returns a new dict in which each value appears exactly once.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 *
 * @return array<Tk, Tv>
 */
function unique(iterable $iterable): array
{
    return unique_by(
        $iterable,
        /**
         * @param Tv $v
         *
         * @return Tv
         *
         * @pure
         */
        static fn($v) => $v
    );
}
