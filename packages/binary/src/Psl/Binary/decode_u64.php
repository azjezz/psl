<?php

declare(strict_types=1);

namespace Psl\Binary;

use function strlen;
use function unpack;

/**
 * Decode an unsigned 64-bit integer from a binary string.
 *
 * Note: PHP integers are signed 64-bit, so unsigned values exceeding
 * PHP_INT_MAX (2^63 - 1) cannot be represented and will throw.
 *
 * @throws Exception\UnderflowException If $bytes has fewer than 8 bytes.
 * @throws Exception\OverflowException If the decoded value exceeds PHP_INT_MAX.
 *
 * @return int<0, max>
 *
 * @pure
 */
function decode_u64(string $bytes, Endianness $endianness = Endianness::Big): int
{
    if (strlen($bytes) < 8) {
        throw new Exception\UnderflowException('Expected at least 8 bytes, got ' . strlen($bytes) . '.');
    }

    $value = unpack(match ($endianness) {
        Endianness::Big => 'J',
        Endianness::Little => 'P',
    }, $bytes)[1];

    if ($value < 0) {
        throw new Exception\OverflowException('Decoded u64 value exceeds PHP_INT_MAX and cannot be represented.');
    }

    return $value;
}
