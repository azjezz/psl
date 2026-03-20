<?php

declare(strict_types=1);

namespace Psl\Str;

use function str_pad;

use const STR_PAD_LEFT;

/**
 * Returns the string padded to the total length by appending the `$padString`
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
 * @param non-empty-string $padString
 * @param int<0, max> $totalLength
 *
 * @pure
 */
function pad_left(
    string $string,
    int $totalLength,
    string $padString = ' ',
    Encoding $encoding = Encoding::Utf8,
): string {
    if ($encoding === Encoding::Ascii || $encoding === Encoding::Utf8) {
        if (Byte\length($padString) === namespace\length($padString, $encoding)) {
            // All characters in pad_string are single-byte, str_pad is safe
            return str_pad(
                $string,
                Byte\length($string) + $totalLength - namespace\length($string, $encoding),
                $padString,
                STR_PAD_LEFT,
            );
        }
    }

    $padLength = namespace\length($padString, $encoding);
    do {
        $length = namespace\length($string, $encoding);
        $remaining = $totalLength - $length;
        if ($remaining <= 0) {
            return $string;
        }

        if ($remaining <= $padLength) {
            $padString = namespace\slice($padString, 0, $remaining, $encoding);
            $padLength = $remaining;
        }

        $string = $padString . $string;
    } while (true);
}
