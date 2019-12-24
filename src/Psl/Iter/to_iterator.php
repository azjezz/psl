<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Copy the iterable into an Iterator.
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv> $iterable
 *
 * @psalm-return \Iterator<Tk, Tv>
 */
function to_iterator(iterable $iterable): \Iterator
{
    if ($iterable instanceof \Iterator) {
        /** @var \Iterator<Tk, Tv> $iterator */
        $iterator = $iterable;
    } elseif ($iterable instanceof \IteratorAggregate) {
        /** @var \Iterator<Tk, Tv> $iterator */
        $iterator = $iterable->getIterator();
    } else {
        /** @var \Iterator<Tk, Tv> $iterator */
        $iterator = new Iterator($iterable);
    }

    return $iterator;
}
