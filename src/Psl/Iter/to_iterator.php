<?php

declare(strict_types=1);

namespace Psl\Iter;

use Iterator as IteratorInterface;
use IteratorAggregate;

/**
 * Copy the iterable into an Iterator.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv> $iterable
 *
 * @psalm-return IteratorInterface<Tk, Tv>
 */
function to_iterator(iterable $iterable): IteratorInterface
{
    if ($iterable instanceof IteratorInterface) {
        /** @var IteratorInterface<Tk, Tv> $iterator */
        $iterator = $iterable;
    } elseif ($iterable instanceof IteratorAggregate) {
        /** @var IteratorInterface<Tk, Tv> $iterator */
        $iterator = $iterable->getIterator();
    } else {
        /** @var IteratorInterface<Tk, Tv> $iterator */
        $iterator = new Iterator($iterable);
    }

    return $iterator;
}
