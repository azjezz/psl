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
 * @template T
 *
 * @param iterable<T> $iterable
 *
 * @return int<0, max>
 */
function count(iterable $iterable): int
{
    if (is_countable($iterable)) {
        /** @var int<0, max> */
        return \count($iterable);
    }

    $count = 0;
    foreach ($iterable as $_) {
        ++$count;
    }

    return $count;
}
