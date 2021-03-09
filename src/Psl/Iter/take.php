<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl;
use Psl\Dict;

/**
 * Take the first n elements from an iterable.
 *
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 *
 * @throws Psl\Exception\InvariantViolationException If the $n is negative
 *
 * @return Iterator<Tk, Tv>
 *
 * @deprecated use `Dict\take` instead.
 * @see Dict\take()
 */
function take(iterable $iterable, int $n): Iterator
{
    /** @psalm-suppress DeprecatedFunction */
    return slice($iterable, 0, $n);
}
