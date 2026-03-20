<?php

declare(strict_types=1);

namespace Psl\Binary;

use function strlen;
use function unpack;

/**
 * Decode an unsigned 16-bit integer from a binary string.
 *
 * @throws Exception\UnderflowException If $bytes has fewer than 2 bytes.
 *
 * @return int<0, 65535>
 *
 * @pure
 */
function decode_u16(string $bytes, Endianness $endianness = Endianness::Big): int
{
    if (strlen($bytes) < 2) {
        throw new Exception\UnderflowException('Expected at least 2 bytes, got ' . strlen($bytes) . '.');
    }

    return unpack(match ($endianness) {
        Endianness::Big => 'n',
        Endianness::Little => 'v',
    }, $bytes)[1];
}
