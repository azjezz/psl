<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Vec;

/**
 * Filter out null values from the given iterable.
 *
 * Example:
 *      Arr\filter_nulls([1, null, 5])
 *      => Arr(1, 5)
 *
 * @template T
 *
 * @param iterable<T|null> $iterable
 *
 * @return list<T>
 *
 * @deprecated use `Vec\filter_nulls` instead.
 * @see Vec\filter_nulls()
 */
function filter_nulls(iterable $iterable): array
{
    return Vec\filter_nulls($iterable);
}
