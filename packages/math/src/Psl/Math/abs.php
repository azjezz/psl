<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Returns the absolute value of the given number.
 *
 * @pure
 *
 * @api
 */
function abs<T: int|float>(T $number): T
{
    return $number < 0 ? -$number : $number;
}
