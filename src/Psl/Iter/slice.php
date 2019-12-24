<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl;

/**
 * Takes a slice from an iterable.
 *
 * Examples:
 *
 *      Iter\slice([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5)
 *      => iter(0, 1, 2, 3, 4, 5)
 *
 *      IterIter\slice([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5, 3)
 *      => Iter(0, 1, 2, 3)
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk,Tv>     $iterable Iterable to take the slice from
 * @psalm-param int                 $start Start offset
 * @psalm-param int                 $length Length (if not specified all remaining values from the
 *                                      iterable are used)
 *
 * @psalm-return iterable<Tk, Tv>
 */
function slice(iterable $iterable, int $start, ?int $length = null): iterable
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
