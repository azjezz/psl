<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Gen;

/**
 * Converts an iterable of key and value pairs, into an iterator of entries.
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param    iterable<Tk, Tv>    $iterable
 *
 * @psalm-return   Iterator<int, array{0: Tk, 1: Tv}>
 *
 * @see            Gen\enumerate()
 */
function enumerate(iterable $iterable): Iterator
{
    return new Iterator(Gen\enumerate($iterable));
}
