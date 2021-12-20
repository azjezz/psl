<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use function str_split;

/**
 * Returns an array containing the string split into chunks of the given size.
 *
 * @param positive-int $chunk_length maximum length of the chunk
 *
 * @return list<string> if $chunk_length parameter is specified, the returned array will be broken down
 *                      into chunks with each being $chunk_length in length, otherwise each chunk
 *                      will be one character in length.
 *                      If the $chunk_length length exceeds the length of string, the entire string is returned
 *                      as the first (and only) array element.
 *                      If the given string is empty, and empty array will be returned.
 *
 * @pure
 */
function chunk(string $string, int $chunk_length = 1): array
{
    if ('' === $string) {
        return [];
    }

    return str_split($string, $chunk_length);
}
