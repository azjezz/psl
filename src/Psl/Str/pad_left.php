<?php

declare(strict_types=1);

namespace Psl\Str;

use function str_pad;

use const STR_PAD_LEFT;

/**
 * Returns the string padded to the total length by appending the `$pad_string`
 * to the left.
 *
 * If the length of the input string plus the pad string exceeds the total
 * length, the pad string will be truncated. If the total length is less than or
 * equal to the length of the input string, no padding will occur.
 *
 * Example:
 *
 *      Str\pad_left('Ay', 4)
 *      => Str('  Ay')
 *
 *      Str\pad_left('ay', 3, 'A')
 *      => Str('Aay')
 *
 *      Str\pad_left('eet', 4, 'Yeeeee')
 *      => Str('Yeet')
 *
 *      Str\pad_left('مرحبا', 8, 'م')
 *      => Str('ممممرحبا')
 *
 * @param non-empty-string $pad_string
 * @param int<0, max> $total_length
 *
 * @pure
 */
function pad_left(
    string $string,
    int $total_length,
    string $pad_string = ' ',
    Encoding $encoding = Encoding::Utf8,
): string {
    if ($encoding === Encoding::Ascii || $encoding === Encoding::Utf8) {
        if (Byte\length($pad_string) === length($pad_string, $encoding)) {
            // All characters in pad_string are single-byte, str_pad is safe
            return str_pad(
                $string,
                Byte\length($string) + $total_length - length($string, $encoding),
                $pad_string,
                STR_PAD_LEFT,
            );
        }
    }

    $pad_length = length($pad_string, $encoding);
    do {
        $length = length($string, $encoding);
        $remaining = $total_length - $length;
        if ($remaining <= 0) {
            return $string;
        }

        if ($remaining <= $pad_length) {
            $pad_string = slice($pad_string, 0, $remaining, $encoding);
            $pad_length = $remaining;
        }

        $string = $pad_string . $string;
    } while (true);
}
