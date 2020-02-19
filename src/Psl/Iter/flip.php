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
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv> $iterable
 *
 * @psalm-return Generator<Tv, Tk, mixed, void>
 */
function flip(iterable $iterable): Generator
{
    foreach ($iterable as $k => $v) {
        yield $v => $k;
    }
}
