<?php

declare(strict_types=1);

namespace Psl\Str;

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
 *      => Str('Yeet')
 *
 *      Str\pad_right('مرحبا', 8, 'ا')
 *      => Str('مرحباااا')
 *
 * @param non-empty-string $pad_string
 * @param int<0, max> $total_length
 *
 * @pure
 */
function pad_right(string $string, int $total_length, string $pad_string = ' ', Encoding $encoding = Encoding::UTF_8): string
{
    while (($length = length($string, $encoding)) < $total_length) {
        /** @var int<0, max> $remaining */
        $remaining = $total_length - $length;
        if ($remaining <= length($pad_string, $encoding)) {
            $pad_string = slice($pad_string, 0, $remaining, $encoding);
        }

        $string .= $pad_string;
    }

    return $string;
}
