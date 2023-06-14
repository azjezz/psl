<?php

declare(strict_types=1);

namespace Psl\Encoding\Base64;

use Psl\Encoding\Exception;

/**
 * Decode a base64-encoded string into raw binary.
 *
 * Base64 character set:
 *  [A-Z]      [a-z]      [0-9]      +     /
 *  0x41-0x5a, 0x61-0x7a, 0x30-0x39, 0x2b, 0x2f
 *
 * @pure
 *
 * @throws Exception\RangeException If the encoded string contains characters outside
 *                                  the base64 characters range.
 * @throws Exception\IncorrectPaddingException If the encoded string has an incorrect padding.
 */
function decode(string $base64, Variant $variant = Variant::Default, bool $strictPadding = true): string
{
    return match ($variant) {
        Variant::Default => Internal\Base64::decode($base64, $strictPadding),
        Variant::UrlSafe => Internal\Base64UrlSafe::decode($base64, $strictPadding),
        Variant::DotSlash => throw new \Exception('To be implemented'),
        Variant::DotSlashOrdered => throw new \Exception('To be implemented'),
    };
}
