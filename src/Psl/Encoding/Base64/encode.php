<?php

declare(strict_types=1);

namespace Psl\Encoding\Base64;

use Exception;

/**
 * Convert a binary string into a base64-encoded string.
 *
 * Base64 character set:
 *  [A-Z]      [a-z]      [0-9]      +     /
 *  0x41-0x5a, 0x61-0x7a, 0x30-0x39, 0x2b, 0x2f
 *
 * @pure
 */
function encode(string $binary, Variant $variant = Variant::Default, bool $padding = true): string
{
    return match ($variant) {
        Variant::Default => Internal\Base64::encode($binary, $padding),
        Variant::UrlSafe => Internal\Base64UrlSafe::encode($binary, $padding),
        Variant::DotSlash => throw new Exception('To be implemented'),
        Variant::DotSlashOrdered => throw new Exception('To be implemented'),
    };
}
