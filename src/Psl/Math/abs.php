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
 * @template T of int|float
 *
 * @param T $number
 *
 * @return T
 *
 * @pure
 *
 * @see https://github.com/vimeo/psalm/issues/2152
 *
 * @psalm-suppress InvalidReturnType
 * @psalm-suppress InvalidReturnStatement
 */
function abs(int|float $number): int|float
{
    return $number < 0 ? -$number : $number;
}
