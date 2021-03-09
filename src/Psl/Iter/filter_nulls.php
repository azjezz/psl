<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl\Vec;

/**
 * Filter out null values from the given iterable.
 *
 * Example:
 *      Iter\filter_nulls([1, null, 5])
 *      => Iter(1, 5)
 *
 * @template T
 *
 * @param iterable<T|null> $iterable
 *
 * @return Iterator<int, T>
 *
 * @deprecated since 1.2, use Vec\filter_nulls instead.
 * @see Vec\filter_nulls()
 */
function filter_nulls(iterable $iterable): Iterator
{
    return Iterator::from(static function () use ($iterable): Generator {
        foreach ($iterable as $value) {
            if (null !== $value) {
                yield $value;
            }
        }
    });
}
