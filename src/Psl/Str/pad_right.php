<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

/**
 * Returns the string padded to the total length by appending the `$pad_string`
 * to the right.
 *
 * If the length of the input string plus the pad string exceeds the total
 * length, the pad string will be truncated. If the total length is less than or
 * equal to the length of the input string, no padding will occur.
 *
 * Example:
 *
 *      Str\pad_right('Ay', 4)
 *      => Str('Ay  ')
 *
 *      Str\pad_right('Ay', 5, 'y')
 *      => Str('Ayyyy')
 *
 *      Str\pad_right('Yee', 4, 't')
 *      => Str('Yeeeeet')
 *
 *      Str\pad_right('مرحبا', 8, 'ا')
 *      => Str('مرحباااا')
 */
function pad_right(string $string, int $total_length, string $pad_string = ' '): string
{
    Psl\invariant('' !== $pad_string, 'Expected non-empty pad string.');
    Psl\invariant($total_length >= 0, 'Expected non-negative total length.');

    while (length($string) < $total_length) {
        $remaining = $total_length - length($string);
        if ($remaining <= length($pad_string)) {
            $pad_string = slice($pad_string, 0, $remaining);
        }

        $string .= $pad_string;
    }

    return $string;
}
