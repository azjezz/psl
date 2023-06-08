<?php

declare(strict_types=1);

namespace Psl\Encoding\Base64;

use function strtr;

/**
 * Convert a binary string into a url safe base64-encoded string.
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
