<?php

declare(strict_types=1);

namespace Psl\Iter;

use function is_countable;

/**
 * Returns the number of elements an iterable contains.
 *
 * This function is not recursive, it counts only the number of elements in the
 * iterable itself, not its children.
 *
 * If the iterable implements Countable its count() method will be used.
 *
 * Examples:
 *
 *      Iter\count([1, 2, 3])
 *      => Int(3)
 *
 *      Iter\count(Iter\flatten([[1, 2, 3], [4], [5, 6], [7, 8]]))
 *      => Int(8)
 *
 * @template T
 *
 * @param iterable<T> $iterable
 */
function count(iterable $iterable): int
{
    if (is_countable($iterable)) {
        return \count($iterable);
    }

    $count = 0;
    foreach ($iterable as $_) {
        ++$count;
    }

    return $count;
}
