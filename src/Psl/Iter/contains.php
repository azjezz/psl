<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Returns true if the given iterable contains the value. Strict equality is
 * used.
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
