<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;

/**
 * Returns an iterator where each mapping is defined by the given key/value
 * tuples.
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param iterable<array{0: Tk, 1: Tv}> $entries
 *
 * @psalm-return Iterator<Tk, Tv>
 */
function from_entries(iterable $entries): Iterator
{
    return Iterator::from(static function () use ($entries): Generator {
        foreach ($entries as [$key, $value]) {
            yield $key => $value;
        }
    });
}
