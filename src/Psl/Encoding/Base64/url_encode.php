<?php

declare(strict_types=1);

namespace Psl\Encoding\Base64;

/**
 * Convert a binary string into a base64-encoded string url safe.
 *
 * Base64url character set:
 *  [A-Z]      [a-z]      [0-9]      -     _
 *  0x41-0x5a, 0x61-0x7a, 0x30-0x39, 0x2d, 0x5f
 *
 * @pure
 */
function url_encode(string $binary): string
{
    return strtr(encode($binary), '+/', '-_');
}
