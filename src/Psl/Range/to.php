<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * @template T of int|float
 *
 * @param T $upper_bound
 *
 * @return ToRange<T>
 *
 * @psalm-mutation-free
 */
function to(int|float $upper_bound, bool $inclusive = false): ToRange
{
    return new ToRange($upper_bound, $inclusive);
}
