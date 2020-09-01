<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl;

/**
 * Take the first n elements from an iterable.
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv> $iterable
 *
 * @psalm-return Iterator<Tk, Tv>
 *
 * @throws Psl\Exception\InvariantViolationException If the $n is negative
 */
function take(iterable $iterable, int $n): Iterator
{
    return slice($iterable, 0, $n);
}
