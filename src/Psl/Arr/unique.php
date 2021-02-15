<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Dict;

/**
 * Returns a new array in which each value appears exactly once.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv> $iterable
 *
 * @psalm-return array<Tk, Tv>
 *
 * @deprecated use `Dict\unique` instead
 *
 * @see Dict\unique
 */
function unique(iterable $iterable): array
{
    return Dict\unique($iterable);
}
