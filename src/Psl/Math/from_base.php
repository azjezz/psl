<?php

declare(strict_types=1);

namespace Psl\Math;

use Psl;
use Psl\Str\Byte;

/**
 * Converts the given string in the given base to an int, assuming letters a-z
 * are used for digits when `$from_base` > 10.
 *
 *  Example:
 *
 *      Math\from_base('10', 2)
 *      => Int(2)
 *
 *      Math\from_base('ff', 15)
 *      => Int(255)
 *
 * @param non-empty-string $number
 *
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If $number is empty, $from_base is outside the [2, 36] range,
 *                                                   or $number is invalid.
 */
function from_base(string $number, int $from_base): int
{
    Psl\invariant('' !== $number, 'Unexpected empty string, expected number in base %d', $from_base);
    Psl\invariant(
        $from_base >= 2 && $from_base <= 36,
        'Expected $from_base to be between 2 and 36, got %d',
        $from_base
    );

    /** @psalm-suppress MissingThrowsDocblock */
    $limit  = div(INT64_MAX, $from_base);
    $result = 0;
    foreach (Byte\chunk($number) as $digit) {
        $oval = Byte\ord($digit);
        // Branches sorted by guesstimated frequency of use. */
        if (/* '0' - '9' */ $oval <= 57 && $oval >= 48) {
            $dval = $oval - 48;
        } elseif (/* 'a' - 'z' */ $oval >= 97 && $oval <= 122) {
            $dval = $oval - 87;
        } elseif (/* 'A' - 'Z' */ $oval >= 65 && $oval <= 90) {
            $dval = $oval - 55;
        } else {
            $dval = 99;
        }

        Psl\invariant($dval < $from_base, 'Invalid digit %s in base %d', $digit, $from_base);
        $oldval = $result;
        $result = $from_base * $result + $dval;
        Psl\invariant(
            $oldval <= $limit && $result >= $oldval,
            'Unexpected integer overflow parsing %s from base %d',
            $number,
            $from_base
        );
    }

    return $result;
}
