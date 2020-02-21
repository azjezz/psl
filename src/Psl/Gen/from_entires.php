<?php

declare(strict_types=1);

namespace Psl\Gen;

use Generator;

/**
 * Returns a generator where each mapping is defined by the given key/value
 * tuples.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<array{0: Tk, 1: Tv}> $entries
 *
 * @psalm-return Generator<Tk, Tv, mixed, void>
 */
function from_entries(iterable $entries): Generator
{
    foreach ($entries as [$key, $value]) {
        yield $key => $value;
    }
}
