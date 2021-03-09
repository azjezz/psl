<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Dict;

/**
 * Returns a new array in which each value appears exactly once.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 *
 * @return array<Tk, Tv>
 *
 * @deprecated use `Dict\unique` instead
 * @see Dict\unique
 */
function unique(iterable $iterable): array
{
    return Dict\unique($iterable);
}
