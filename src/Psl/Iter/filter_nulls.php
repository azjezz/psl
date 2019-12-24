<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Filter out null values from the given iterable.
 *
 * Example:
 *      Iter\filter_nulls([1, null, 5])
 *      => Iter(1, 5)
 *
 * @psalm-template T
 *
 * @psalm-param iterable<null|T> $iterable
 *
 * @psalm-return iterable<T>
 */
function filter_nulls(iterable $iterable): iterable
{
    foreach ($iterable as $value) {
        if (null !== $value) {
            yield $value;
        }
    }
}
