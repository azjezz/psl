<?php

declare(strict_types=1);

namespace Psl\Iter;

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
 * @psalm-return iterable<Tv, Tk>
 */
function flip(iterable $iterable): iterable
{
    foreach ($iterable as $k => $v) {
        yield $v => $k;
    }
}
