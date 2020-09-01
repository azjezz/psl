<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Merges multiple iterables into a lazy iterator.
 *
 * Example:
 *      Iter\merge([1, 2], [9, 8])
 *      => Iter(0 => 1, 1 => 2, 0 => 9, 1 => 8)
 *
 *      Iter\merge([0 => 1, 1 => 2], [2 => 9, 3 => 8])
 *      => Iter(0 => 1, 1 => 2, 2 => 9, 3 => 8)
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv> $first
 * @psalm-param iterable<Tk, Tv> ...$rest
 *
 * @psalm-return Iterator<Tk, Tv>
 */
function merge(iterable $first, iterable ...$rest): Iterator
{
    $iterables = [$first];
    foreach ($rest as $iterable) {
        $iterables[] = $iterable;
    }

    return flatten($iterables);
}
