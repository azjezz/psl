<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

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
 * @psalm-param int $chunk_size maximum length of the chunk
 *
 * @psalm-return list<string> if $chunk_size parameter is specified, the returned array will be broken down
 *                  into chunks with each being $chunk_size in length, otherwise each chunk
 *                  will be one character in length.
 *                  If the $chunk_size length exceeds the length of string, the entire string is returned
 *                  as the first (and only) array element.
 *
 * @throws Psl\Exception\InvariantViolationException If the given $chunk_size is negative or above the limit ( 65535 ).
 */
function chunk(string $string, int $chunk_size = 1): array
{
    Psl\invariant($chunk_size >= 1, 'Expected a non-negative chunk size.');
    if ('' === $string) {
        return [];
    }

    Psl\invariant(65535 >= $chunk_size, 'Maximum chunk length must not exceed 65535.');

    /** @psalm-var list<string> */
    return mb_str_split($string, $chunk_size, encoding($string));
}
