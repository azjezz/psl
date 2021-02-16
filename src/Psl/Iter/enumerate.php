<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl\Vec;

/**
 * Converts an iterable of key and value pairs, into a generator of entries.
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv>    $iterable
 *
 * @psalm-return Iterator<int, array{0: Tk, 1: Tv}>
 *
 * @deprecated use `Vec\enumerate` instead.
 *
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
