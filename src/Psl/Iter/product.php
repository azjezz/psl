<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Gen;

/**
 * Returns the cartesian product of iterables that were passed as arguments.
 *
 * The resulting iterator will contain all the possible tuples of keys and
 * values.
 *
 * Examples:
 *
 *      Iter\product(
 *          Iter\range(1, 2),
 *          Iter\range(3, 4)
 *      )
 *     => Iter([0, 0] => [1, 3], [0, 1] => [1, 4], [1, 0] => [2, 3], [1, 1] => [2, 4])
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param    list<iterable<Tk, Tv>>   $iterables Iterables to combine
 *
 * @psalm-return   Iterator<list<Tk>, list<Tv>>
 *
 * @see            Gen\product()
 */
function product(iterable ...$iterables): Iterator
{
    return new Iterator(Gen\product(...$iterables));
}
