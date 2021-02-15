<?php

declare(strict_types=1);

namespace Psl\Dict;

/**
 * Returns a new dict in which each value appears exactly once.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv> $iterable
 *
 * @psalm-return array<Tk, Tv>
 */
function unique(iterable $iterable): array
{
    return unique_by(
        $iterable,
        /**
         * @psalm-param     Tv  $v
         *
         * @psalm-return    Tv
         *
         * @psalm-pure
         */
        static fn($v) => $v
    );
}
