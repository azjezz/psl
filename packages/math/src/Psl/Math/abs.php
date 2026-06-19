<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Returns the absolute value of the given number.
 *
 * @param T $number
 *
 * @return T
 *
 * @pure
 *
 * @api
 */
function abs<T : int|float = int|float>(int|float $number): int|float
{
    return $number < 0 ? -$number : $number;
}
