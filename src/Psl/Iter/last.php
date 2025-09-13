<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Returns the last element of an iterable, if the iterable is empty, null will be returned.
 *
 * @template T
 *
 * @param iterable<T> $iterable
 *
 * @return ($iterable is non-empty-array|non-empty-list ? T : T|null)
 */
function last(iterable $iterable): mixed
{
    $last = null;
    foreach ($iterable as $v) {
        $last = $v;
    }

    return $last;
}
