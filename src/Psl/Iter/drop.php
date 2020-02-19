<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Drops the first n items from an iterable.
 *
 * Examples:
 *
 *      Iter\drop([1, 2, 3, 4, 5], 3)
 *      => Iter(4, 5)
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk,Tv>     $iterable Iterable to drop the elements from
 * @psalm-param int                 $num Number of elements to drop from the start
 *
 * @psalm-return iterable<Tk,Tv>
 */
function drop(iterable $iterable, int $num): iterable
{
    /** @psalm-var iterable<Tk, Tv> */
    return slice($iterable, $num);
}
