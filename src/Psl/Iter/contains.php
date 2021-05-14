<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Returns true if the given iterable contains the value. Strict equality is
 * used.
 *
 * Examples:
 *
 *      Iter\contains(['a', 'b'], 'a')
 *      => true
 *
 *      Iter\contains(Iterable\range(0, 5), 1)
 *      => true
 *
 *      Iter\contains(Iterable\range(0, 5), '1')
 *      => false
 *
 * @template T
 *
 * @param iterable<T> $iterable
 * @param T $value
 */
function contains(iterable $iterable, mixed $value): bool
{
    foreach ($iterable as $v) {
        if ($value === $v) {
            return true;
        }
    }

    return false;
}
