<?php

declare(strict_types=1);

namespace Psl\Encoding\Base64;

/**
 * Convert a binary string into a base64-encoded string.
 *
 * @pure
 */
function encode(string $binary, Variant $variant = Variant::Default, bool $padding = true): string
{
    return match ($variant) {
        Variant::Default => Internal\Base64::encode($binary, $padding),
        Variant::UrlSafe => Internal\Base64UrlSafe::encode($binary, $padding),
        Variant::DotSlash => Internal\Base64DotSlash::encode($binary, $padding),
        Variant::DotSlashOrdered => Internal\Base64DotSlashOrdered::encode($binary, $padding),
    };
}
