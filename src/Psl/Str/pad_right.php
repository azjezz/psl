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
 *      => Str('Ay    ')
 *
 *      Str\pad_right('Ay', 1, 'y')
 *      => Str('Ayy')
 *
 *      Str\pad_right('Yee', 4, 'eeet')
 *      => Str('Yeeeeet')
 *
 *      Str\pad_right('مرحبا', 5, 'م')
 *      => Str('ممممممرحبا')
 */
function pad_right(string $string, int $total_length, string $pad_string = ' '): string
{
    Psl\invariant('' !== $pad_string, 'Expected non-empty pad string.');
    Psl\invariant($total_length >= 0, 'Expected non-negative total length.');

    return Byte\pad_right(
        $string,
        Byte\length($string) - length($string) + $total_length,
        $pad_string
    );
}
