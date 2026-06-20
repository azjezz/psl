<?php

declare(strict_types=1);

namespace Psl\Iter;

use function count as php_count;
use function is_countable;

/**
 * Returns the number of elements an iterable contains.
 *
 * This function is not recursive, it counts only the number of elements in the
 * iterable itself, not its children.
 *
 * If the iterable implements Countable its count() method will be used.
 *
 * @param iterable<T> $iterable
 *
 * @return ($iterable is non-empty-array|non-empty-list ? int<1, max> : int<0, max>)
 *
 * @api
 */
function count<T>(iterable $iterable): int
{
    if (is_countable($iterable)) {
        return php_count($iterable);
    }

    $count = 0;
    foreach ($iterable as $_) {
        ++$count;
    }

    return $count;
}
