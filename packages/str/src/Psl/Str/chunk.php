<?php

declare(strict_types=1);

namespace Psl\Str;

use function mb_str_split;

/**
 * Returns an array containing the string split into chunks of the given size.
 *
 * Example:
 *
 *      Str\chunk('Hello, World')
 *      => Arr('H', 'e', 'l', 'l', 'o', ',', ' ', 'W', 'o', 'r', 'l', 'd')
 *
 *      Str\chunk('PHP', 3)
 *      => Arr('PHP')
 *
 *      Str\chunk('مرحبا')
 *      => Arr('م', 'ر', 'ح', 'ب', 'ا')
 *
 *      Str\chunk('مرحبا', 2)
 *      => Arr('مر', 'حب', 'ا')
 *
 * @param int<1, 65535> $chunkLength maximum length of the chunk
 *
 * @return list<non-empty-string> if $chunkLength parameter is specified, the returned array will be broken down
 *                                into chunks with each being $chunkLength in length, otherwise each chunk will be
 *                                one character in length.
 *                                If the $chunkLength length exceeds the length of string, the entire string is returned
 *                                as the first (and only) array element.
 *
 * @pure
 */
function chunk(string $string, int $chunkLength = 1, Encoding $encoding = Encoding::Utf8): array
{
    if ('' === $string) {
        return [];
    }

    return mb_str_split($string, $chunkLength, $encoding->value);
}
