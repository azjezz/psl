<?php

declare(strict_types=1);

namespace Psl\Iter;

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
 * @psalm-param iterable<array-key, mixed> $iterable
 */
function count(iterable $iterable): int
{
    if ($iterable instanceof \Countable) {
        return $iterable->count();
    }

    $count = 0;
    /** @psalm-var mixed $v */
    foreach ($iterable as $v) {
        ++$count;
    }

    return $count;
}
