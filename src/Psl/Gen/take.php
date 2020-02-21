<?php

declare(strict_types=1);

namespace Psl\Gen;

use Generator;

/**
 * Take the first n elements from an iterable.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv> $iterable
 *
 * @psalm-return Generator<Tk, Tv, mixed, void>
 */
function take(iterable $iterable, int $n): Generator
{
    return slice($iterable, 0, $n);
}
