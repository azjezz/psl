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
 * @throws Exception\ArithmeticException        If the numerator is Math\INT64_MAX and the denominator is -1.
 * @throws Exception\DivisionByZeroException    If the denominator is 0.
 */
function div(int $numerator, int $denominator): int
{
    try {
        return intdiv($numerator, $denominator);
    } catch (ArithmeticError $error) {
        throw new Exception\ArithmeticException($error->getMessage(), $error->getCode(), $error);
    } catch (DivisionByZeroError $error) {
        throw new Exception\DivisionByZeroException($error->getMessage(), $error->getCode(), $error);
    }
}
