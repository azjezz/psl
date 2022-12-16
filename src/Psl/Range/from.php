<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * @template T of int|float
 *
 * @param T $lower_bound
 *
 * @return FromRange<T>
 *
 * @psalm-mutation-free
 */
function from(int|float $lower_bound): FromRange
{
    return new FromRange($lower_bound);
}
