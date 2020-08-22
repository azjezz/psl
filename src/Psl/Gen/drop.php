<?php

declare(strict_types=1);

namespace Psl\Gen;

use Generator;
use Psl;

/**
 * Drops the first n items from an iterable.
 *
 * Examples:
 *
 *      Gen\drop([1, 2, 3, 4, 5], 3)
 *      => Gen(4, 5)
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv>    $iterable   Iterable to drop the elements from
 * @psalm-param int                 $n          Number of elements to drop from the start
 *
 * @psalm-return Generator<Tk, Tv, mixed, void>
 *
 * @throws Psl\Exception\InvariantViolationException If the $n is negative
 */
function drop(iterable $iterable, int $n): Generator
{
    return slice($iterable, $n);
}
