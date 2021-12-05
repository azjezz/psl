<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Returns the absolute value of the given number.
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
