<?php

declare(strict_types=1);

namespace Psl\Math;

use Psl;
use Psl\Str;

/**
 * Converts the given non-negative number into the given base, using letters a-z
 * for digits when then given base is > 10.
 */
function to_base(int $number, int $base): string
{
    Psl\invariant($base >= 2 && $base <= 36, 'Expected $to_base to be between 2 and 36, got %d', $base);
    Psl\invariant($number >= 0, 'Expected non-negative base conversion input, got %d', $number);
    $result = '';
    do {
        $quotient = div($number, $base);
        $result = Str\ALPHABET_ALPHANUMERIC[$number - $quotient * $base] . $result;
        $number = $quotient;
    } while (0 !== $number);

    return $result;
}
