<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Returns the first element of an iterable, if the iterable is empty, null will be returned.
 *
 * @template T
 *
 * @param iterable<T> $iterable
 *
 * @return T|null
 */
function first(iterable $iterable)
{
    foreach ($iterable as $v) {
        return $v;
    }

    return null;
}
