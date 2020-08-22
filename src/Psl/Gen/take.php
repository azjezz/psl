<?php

declare(strict_types=1);

namespace Psl\Gen;

use Generator;
use Psl;

/**
 * Take the first n elements from an iterable.
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv> $iterable
 *
 * @psalm-return Generator<Tk, Tv, mixed, void>
 *
 * @throws Psl\Exception\InvariantViolationException If the $n is negative
 */
function take(iterable $iterable, int $n): Generator
{
    return slice($iterable, 0, $n);
}
