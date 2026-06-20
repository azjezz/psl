<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Copy the iterable into an Iterator.
 *
 * @param iterable<Tk, Tv> $iterable
 *
 * @see Iterator
 *
 * @api
 */
function to_iterator<Tk, Tv>(iterable $iterable): Iterator<Tk, Tv>
{
    return Iterator::<Tk, Tv>::create($iterable);
}
