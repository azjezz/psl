<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl\Dict;

/**
 * Returns an iterator where each mapping is defined by the given key/value
 * tuples.
 *
 * @template Tk
 * @template Tv
 *
 * @param iterable<array{0: Tk, 1: Tv}> $entries
 *
 * @return Iterator<Tk, Tv>
 *
 * @deprecated use `Dict\from_entries` instead.
 * @see Dict\from_entries()
 */
function from_entries(iterable $entries): Iterator
{
    return Iterator::from(static function () use ($entries): Generator {
        foreach ($entries as [$key, $value]) {
            yield $key => $value;
        }
    });
}
