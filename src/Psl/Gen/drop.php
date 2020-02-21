<?php

declare(strict_types=1);

namespace Psl\Gen;

/**
 * Drops the first n items from an iterable.
 *
 * Examples:
 *
 *      Gen\drop([1, 2, 3, 4, 5], 3)
 *      => Gen(4, 5)
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk,Tv>     $iterable Iterable to drop the elements from
 * @psalm-param int                 $num Number of elements to drop from the start
 *
 * @psalm-return Generator<Tk, Tv, mixed, void>
 */
function drop(iterable $iterable, int $num): iterable
{
    return slice($iterable, $num);
}
