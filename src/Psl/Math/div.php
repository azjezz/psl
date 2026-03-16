<?php

declare(strict_types=1);

namespace Psl\Math;

use ArithmeticError;
use DivisionByZeroError;

use function intdiv;
use function sprintf;

/**
 * Returns the result of integer division of the given numerator by the given denominator.
 *
 * @pure
 *
 * @throws Exception\ArithmeticException If the $numerator is Math\INT64_MIN and the $denominator is -1.
 * @throws Exception\DivisionByZeroException If the $denominator is 0.
 */
function div(int $numerator, int $denominator): int
{
    try {
        return intdiv($numerator, $denominator);
    } catch (DivisionByZeroError $error) { // @mago-expect analysis:avoid-catching-error
        throw new Exception\DivisionByZeroException(sprintf('%s.', $error->getMessage()), $error->getCode(), $error);
    } catch (ArithmeticError $error) { // @mago-expect analysis:avoid-catching-error
        throw new Exception\ArithmeticException(
            'Division of Math\INT64_MIN by -1 is not an integer.',
            $error->getCode(),
            $error,
        );
    }
}
