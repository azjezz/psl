<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

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
 * @param non-empty-string $padString
 * @param int<0, max> $totalLength
 *
 * @pure
 */
function pad_left(string $string, int $totalLength, string $padString = ' '): string
{
    return str_pad($string, $totalLength, $padString, STR_PAD_LEFT);
}
