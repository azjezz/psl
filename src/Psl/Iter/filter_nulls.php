<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Gen;

/**
 * Filter out null values from the given iterable.
 *
 * Example:
 *      Iter\filter_nulls([1, null, 5])
 *      => Iter(1, 5)
 *
 * @psalm-template T
 *
 * @psalm-param    iterable<null|T> $iterable
 *
 * @psalm-return   Iterator<int, T>
 *
 * @see            Gen\filter_nulls()
 */
function filter_nulls(iterable $iterable): Iterator
{
    return new Iterator(Gen\filter_nulls($iterable));
}
