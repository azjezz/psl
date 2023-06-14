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
 * Base64url character set:
 * [A-Z]      [a-z]      [0-9]      -     _
 * 0x41-0x5a, 0x61-0x7a, 0x30-0x39, 0x2d, 0x5f
 *
 * Base64dotSlash character set:
 * ./         [A-Z]      [a-z]     [0-9]
 * 0x2e-0x2f, 0x41-0x5a, 0x61-0x7a, 0x30-0x39
 *
 * Base64dotSlashOrdered character set:
 * [.-9]      [A-Z]      [a-z]
 * 0x2e-0x39, 0x41-0x5a, 0x61-0x7a
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
        Variant::DotSlash => Internal\Base64DotSlash::decode($base64, $strictPadding),
        Variant::DotSlashOrdered => Internal\Base64DotSlashOrdered::decode($base64, $strictPadding),
    };
}
