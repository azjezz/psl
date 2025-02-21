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
 *
 * @mago-ignore best-practices/loop-does-not-iterate
 */
function first(iterable $iterable): mixed
{
    foreach ($iterable as $v) {
        return $v;
    }

    return null;
}
