<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Converts the given non-negative number into the given base, using letters a-z
 * for digits when then given base is > 10.
 *
 * @param int<0, max> $number
 * @param int<2, 36> $base
 *
 * @return non-empty-string
 *
 * @pure
 */
function to_base(int $number, int $base): string
{
    $alphabet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $result = '';
    do {
        $quotient = namespace\div($number, $base);
        /** @var int<0, 61> $index */
        $index = $number - ($quotient * $base);
        $result = $alphabet[$index] . $result;
        $number = $quotient;
    } while (0 !== $number);

    return $result;
}
