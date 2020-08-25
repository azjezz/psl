<?php

declare(strict_types=1);

namespace Psl\Gen;

use Generator;
use Psl\Arr;
use Psl\Iter;

/**
 * Merges multiple iterables into a generator.
 *
 * Example:
 *      Gen\merge([1, 2], [9, 8])
 *      => Gen(0 => 1, 1 => 2, 0 => 9, 1 => 8)
 *
 *      Gen\merge([0 => 1, 1 => 2], [2 => 9, 3 => 8])
 *      => Gen(0 => 1, 1 => 2, 2 => 9, 3 => 8)
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param    iterable<Tk, Tv> $first
 * @psalm-param    iterable<iterable<Tk, Tv>> $rest
 *
 * @psalm-return Generator<Tk, Tv, mixed, void>
 */
function merge(iterable $first, iterable ...$rest): Generator
{
    /** @psalm-var list<iterable<Tk, Tv>> $rest */
    $rest = Iter\to_array($rest);
    /** @psalm-var list<iterable<Tk, Tv>> $iterables */
    $iterables = [$first, ...$rest];

    return flatten($iterables);
}
