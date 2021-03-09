<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl\Vec;

/**
 * Converts an iterable of key and value pairs, into a generator of entries.
 *
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 *
 * @return Iterator<int, array{0: Tk, 1: Tv}>
 *
 * @deprecated use `Vec\enumerate` instead.
 * @see Vec\enumerate()
 */
function enumerate(iterable $iterable): Iterator
{
    return Iterator::from(static function () use ($iterable): Generator {
        foreach ($iterable as $k => $v) {
            yield [$k, $v];
        }
    });
}
