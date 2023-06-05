<?php

declare(strict_types=1);

namespace Psl\Encoding\Base64;

use Psl\Encoding\Exception;

/**
 * Decode a base64url-encoded string into raw binary.
 *
 * Base64url character set:
 *  [A-Z]      [a-z]      [0-9]      -     _
 *  0x41-0x5a, 0x61-0x7a, 0x30-0x39, 0x2d, 0x5f
 *
 * @pure
 *
 * @throws Exception\RangeException If the encoded string contains characters outside
 *                                  the base64url characters range.
 * @throws Exception\IncorrectPaddingException If the encoded string has an incorrect padding.
 */
function url_decode(string $base64url): string
{
    /** @var string */
    return decode(strtr($base64url, '-_', '+/'));
}
