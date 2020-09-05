<?php

declare(strict_types=1);

namespace Psl\Math;

use ArithmeticError;
use DivisionByZeroError;

use function intdiv;

/**
 * Returns the result of integer division of the given numerator by the given denominator.
 *
 * Example:
 *
 *      Math\div(10, 2)
 *      => Int(5)
 *
 *      Math\div(5, 2)
 *      => Int(2)
 *
 *      Math\div(15, 20)
 *      => Int(0)
 *
 * @psalm-pure
 *
 * @throws DivisionByZeroError If the denominator is 0
 * @throws ArithmeticError If the numerator is Math\INT64_MAX and the denominator is -1
 */
function div(int $numerator, int $denominator): int
{
    return intdiv($numerator, $denominator);
}
