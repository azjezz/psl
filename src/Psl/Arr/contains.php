<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Iter;

/**
 * Returns true if the given iterable contains the value. Strict equality is
 * used.
 *
 * @see Iter\contains()
 *
 * @psalm-template T
 *
 * @psalm-param iterable<T>    $iterable
 * @psalm-param T           $value
 */
function contains(iterable $iterable, $value): bool
{
    return Iter\contains($iterable, $value);
}
