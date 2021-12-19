<?php

declare(strict_types=1);

namespace Psl\Math;

use Psl\Str;

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
    $result = '';
    do {
        /** @psalm-suppress MissingThrowsDocblock */
        $quotient = div($number, $base);
        $result   = Str\ALPHABET_ALPHANUMERIC[$number - $quotient * $base] . $result;
        $number   = $quotient;
    } while (0 !== $number);

    /** @var non-empty-string */
    return $result;
}
