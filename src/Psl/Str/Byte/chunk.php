<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use Psl;

/**
 * Returns an array containing the string split into chunks of the given size.
 *
 * @psalm-param int $chunk_size maximum length of the chunk
 *
 * @psalm-return list<string> if $chunk_size parameter is specified, the returned array will be broken down
 *                  into chunks with each being $chunk_size in length, otherwise each chunk
 *                  will be one character in length.
 *                  If the $chunk_size length exceeds the length of string, the entire string is returned
 *                  as the first (and only) array element.
 *                  If the given string is empty, and empty array will be returned.
 *
 * @psalm-pure
 *
 * @throws Psl\Exception\InvariantViolationException If $chunk_size is negative.
 */
function chunk(string $string, int $chunk_size = 1): array
{
    Psl\invariant($chunk_size >= 1, 'Expected a non-negative chunk size.');
    if ('' === $string) {
        return [];
    }

    return \str_split($string, $chunk_size);
}
