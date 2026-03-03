<?php

declare(strict_types=1);

namespace Psl\Binary;

use Psl\Str\Byte;

/**
 * Decode an unsigned 8-bit integer from a binary string.
 *
 * @throws Exception\UnderflowException If $bytes has fewer than 1 byte.
 *
 * @return int<0, 255>
 *
 * @pure
 */
function decode_u8(string $bytes): int
{
    if (Byte\length($bytes) < 1) {
        throw new Exception\UnderflowException('Expected at least 1 byte, got ' . Byte\length($bytes) . '.');
    }

    return unpack('C', $bytes)[1];
}
