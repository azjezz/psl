<?php

declare(strict_types=1);

namespace Psl\Encoding\Base64;

use Psl\Encoding\Exception;

/**
 * Decode a base64-encoded string into raw binary.
 *
 * @pure
 *
 * @throws Exception\RangeException If the encoded string contains characters outside
 *                                  the base64 characters range.
 * @throws Exception\IncorrectPaddingException If the encoded string has an incorrect padding.
 */
function decode(string $base64, Variant $variant = Variant::Default, bool $explicit_padding = true): string
{
    return match ($variant) {
        Variant::Default => Internal\Base64::decode($base64, $explicit_padding),
        Variant::UrlSafe => Internal\Base64UrlSafe::decode($base64, $explicit_padding),
        Variant::DotSlash => Internal\Base64DotSlash::decode($base64, $explicit_padding),
        Variant::DotSlashOrdered => Internal\Base64DotSlashOrdered::decode($base64, $explicit_padding),
    };
}
