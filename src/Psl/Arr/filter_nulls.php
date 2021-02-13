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
 * @psalm-template T
 *
 * @psalm-param iterable<T|null> $iterable
 *
 * @psalm-return list<T>
 *
 * @deprecated since 1.2, use Vec\filter_nulls instead.
 */
function filter_nulls(iterable $iterable): array
{
    return Vec\filter_nulls($iterable);
}
