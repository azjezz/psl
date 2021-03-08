<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Gets the first value of an iterable.
 *
 * Example:
 *
 *      Iter\first(Iter\range(5, 10)
 *      => Int(5)
 *
 *      Iter\first(['a' ,'b'])
 *      => Str('a')
 *
 *      Iter\first([])
 *      => Null
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
