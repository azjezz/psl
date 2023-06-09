<?php

declare(strict_types=1);

namespace Psl\Encoding\Base64;

use Psl\Str;

use function base64_encode;

/**
 * Convert a binary string into a base64-encoded string.
 *
 * Base64 character set:
 *  [A-Z]      [a-z]      [0-9]      +     /
 *  0x41-0x5a, 0x61-0x7a, 0x30-0x39, 0x2b, 0x2f
 *
 * @pure
 */
function encode(string $binary, bool $padding = true): string
{
    $base64 = base64_encode($binary);

    if (!$padding) {
        $base64 = Str\trim_right($base64, '=');
    }

    return $base64;
}
