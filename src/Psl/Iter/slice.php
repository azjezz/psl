<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl;
use Psl\Dict;

/**
 * Takes a slice from an iterable.
 *
 * Examples:
 *
 *      Iter\slice([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5)
 *      => Iter(0, 1, 2, 3, 4, 5)
 *
 *      Iter\slice([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5, 3)
 *      => Iter(0, 1, 2)
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk,Tv>     $iterable Iterable to take the slice from
 * @psalm-param int                 $start Start offset
 * @psalm-param int                 $length Length (if not specified all remaining values from the iterable are used)
 *
 * @psalm-return Iterator<Tk, Tv>
 *
 * @throws Psl\Exception\InvariantViolationException If the $start offset or $length are negative
 *
 * @deprecated use `Dict\slice` instead.
 *
 * @see Dict\slice()
 */
function slice(iterable $iterable, int $start, ?int $length = null): Iterator
{
    Psl\invariant($start >= 0, 'Start offset must be non-negative.');
    Psl\invariant(null === $length || $length >= 0, 'Length must be non-negative.');

    return Iterator::from(static function () use ($iterable, $start, $length): Generator {
        if (0 === $length) {
            /** @psalm-suppress InvalidReturnStatement */
            return;
        }

        $i = 0;
        foreach ($iterable as $key => $value) {
            if ($i++ < $start) {
                continue;
            }

            yield $key => $value;
            if (null !== $length && $i >= $start + $length) {
                break;
            }
        }
    });
}
