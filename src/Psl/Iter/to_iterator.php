<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Copy the iterable into an Iterator.
 *
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 *
 * @return Iterator<Tk, Tv>
 *
 * @see Iterator
 */
function to_iterator(iterable $iterable): Iterator
{
    return Iterator::create($iterable);
}
