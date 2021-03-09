<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;

/**
 * Flips the keys and values of an iterable.
 *
 * Examples:
 *
 *      Iter\flip(['a' => 1, 'b' => 2, 'c' => 3])
 *      => Iter(1 => 'a', 2 => 'b', 3 => 'c')
 *
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 *
 * @return Iterator<Tv, Tk>
 *
 * @deprecated use `Dict\flip` instead.
 * @see Dict\flip()
 */
function flip(iterable $iterable): Iterator
{
    return Iterator::from(static function () use ($iterable): Generator {
        foreach ($iterable as $k => $v) {
            yield $v => $k;
        }
    });
}
