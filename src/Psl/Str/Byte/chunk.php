<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use Psl;

/**
 * Returns an array containing the string split into chunks of the given size.
 *
 * @psalm-param int $chunk_size maximum length of the chunk
 *
 * @psalm-return array<int, string> if $chunk_size parameter is specified, the returned array will be broken down
 *                  into chunks with each being $chunk_size in length, otherwise each chunk
 *                  will be one character in length.
 *                  If the $chunk_size length exceeds the length of string, the entire string is returned
 *                  as the first (and only) array element.
 */
function chunk(string $string, int $chunk_size = 1): array
{
    Psl\invariant($chunk_size >= 1, 'Expected positive chunk size.');

    return \str_split($string, $chunk_size);
}
