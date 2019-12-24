<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Returns a new dict where each mapping is defined by the given key/value
 * tuples.
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<array{0: Tk, 1: Tv}> $entries
 *
 * @psalm-return iterable<Tk, Tv>
 */
function from_entries(iterable $entries): iterable
{
    foreach ($entries as [$key, $value]) {
        yield $key => $value;
    }
}
