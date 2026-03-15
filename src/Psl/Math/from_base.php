<?php

declare(strict_types=1);

namespace Psl\Math;

use Psl\Str;
use Psl\Str\Byte;

/**
 * Converts the given string in base `$fromBase` to an integer, assuming letters a-z
 * are used for digits when `$fromBase` > 10.
 *
 * @param non-empty-string $number
 * @param int<2, 36> $fromBase
 *
 * @pure
 *
 * @throws Exception\InvalidArgumentException If $number contains an invalid digit in base $fromBase
 * @throws Exception\OverflowException In case of an integer overflow
 */
function from_base(string $number, int $fromBase): int
{
    $limit = div(INT64_MAX, $fromBase);
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

        if ($fromBase < $dval) {
            throw new Exception\InvalidArgumentException(Str\format('Invalid digit %s in base %d', $digit, $fromBase));
        }

        $oldval = $result;
        $result = ($fromBase * $result) + $dval;
        if ($oldval > $limit || $oldval > $result) {
            throw new Exception\OverflowException(Str\format(
                'Unexpected integer overflow parsing %s from base %d',
                $number,
                $fromBase,
            ));
        }
    }

    return $result;
}
