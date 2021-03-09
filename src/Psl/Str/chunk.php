<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;
use Psl\Internal;

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
 * @param int $chunk_length maximum length of the chunk
 *
 * @throws Psl\Exception\InvariantViolationException If the given $chunk_length is negative or above
 *                                                   the limit ( 65535 ), or an invalid $encoding is provided.
 *
 * @return list<string> if $chunk_length parameter is specified, the returned array will be broken down
 *                      into chunks with each being $chunk_length in length, otherwise each chunk will be
 *                      one character in length.
 *                      If the $chunk_length length exceeds the length of string, the entire string is returned
 *                      as the first (and only) array element.
 *
 * @pure
 */
function chunk(string $string, int $chunk_length = 1, ?string $encoding = null): array
{
    Psl\invariant($chunk_length >= 1, 'Expected a non-negative chunk size.');
    if ('' === $string) {
        return [];
    }

    Psl\invariant(65535 >= $chunk_length, 'Maximum chunk length must not exceed 65535.');

    /** @var list<string> */
    return mb_str_split($string, $chunk_length, Internal\internal_encoding($encoding));
}
