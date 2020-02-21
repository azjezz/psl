<?php

declare(strict_types=1);

namespace Psl\Gen;

use Generator;
use Psl;

/**
 * Takes a slice from an iterable.
 *
 * Examples:
 *
 *      Gen\slice([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5)
 *      => Gen(0, 1, 2, 3, 4, 5)
 *
 *      Gen\slice([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5, 3)
 *      => Gen(0, 1, 2, 3)
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk,Tv>     $iterable Iterable to take the slice from
 * @psalm-param int                 $start Start offset
 * @psalm-param int                 $length Length (if not specified all remaining values from the
 *                                      iterable are used)
 *
 * @psalm-return Generator<Tk, Tv, mixed, void>
 */
function slice(iterable $iterable, int $start, ?int $length = null): Generator
{
    Psl\invariant($start >= 0, 'Start offset must be non-negative');
    Psl\invariant(null === $length || $length >= 0, 'Length must be non-negative');
    if (0 === $length) {
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
}
