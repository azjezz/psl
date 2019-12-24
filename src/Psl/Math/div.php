<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Returns the result of integer division of the given numerator by the given denominator.
 *
 * If the denominator is 0, a DivisionByZeroError exception is thrown.
 * If the numerator is Math\INT64_MAX and the denominator is -1 then an ArithmeticError exception is thrown.
 */
function div(int $numerator, int $denominator): int
{
    return \intdiv($numerator, $denominator);
}
