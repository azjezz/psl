<?php

declare(strict_types=1);

namespace Psl\Encoding\Hex;

/**
 * Convert a binary string into a hexadecimal string.
 *
 * Hex ( Base16 ) character set:
 *  [0-9]      [a-f]
 *  0x30-0x39, 0x61-0x66
 *
 * @pure
 */
function encode(string $binary): string
{
    return bin2hex($binary);
}
