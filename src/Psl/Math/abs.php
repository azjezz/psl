<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Return the absolute value of the given number.
 *
 * Example:
 *
 *      Math\abs(5)
 *      => Int(5)
 *
 *      Math\abs(-10)
 *      => Int(10)
 *
 *      Math\abs(-5.5)
 *      => Float(5.5)
 *
 * @psalm-template T as numeric
 *
 * @psalm-param T $number
 *
 * @psalm-return T
 *
 * @see https://github.com/vimeo/psalm/issues/2152
 * @psalm-suppress InvalidReturnType
 * @psalm-suppress InvalidReturnStatement
 */
function abs($number)
{
    return $number < 0 ? -$number : $number;
}
