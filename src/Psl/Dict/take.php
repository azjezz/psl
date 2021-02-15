<?php

declare(strict_types=1);

namespace Psl\Dict;

use Psl;

/**
 * Take the first n elements from an iterable.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv>    $iterable   Iterable to take the elements from
 * @psalm-param int                 $n          Number of elements to take from the start
 *
 * @psalm-return array<Tk, Tv>
 *
 * @throws Psl\Exception\InvariantViolationException If the $n is negative
 */
function take(iterable $iterable, int $n): array
{
    return slice($iterable, 0, $n);
}
