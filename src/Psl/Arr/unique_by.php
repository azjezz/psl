<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Dict;

/**
 * Returns a new array in which each value appears exactly once, where the
 * value's uniqueness is determined by transforming it to a scalar via the
 * given function.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 * @psalm-template Ts
 *
 * @psalm-param iterable<Tk, Tv>   $iterable
 * @psalm-param (callable(Tv): Ts) $scalar_func
 *
 * @psalm-return array<Tk, Tv>
 *
 * @deprecated use `Dict\unique_by` instead
 *
 * @see Dict\unique_by
 */
function unique_by(iterable $iterable, callable $scalar_func): array
{
    return Dict\unique_by($iterable, $scalar_func);
}
