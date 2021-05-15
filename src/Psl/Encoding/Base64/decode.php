<?php

declare(strict_types=1);

namespace Psl\Encoding\Base64;

use Psl\Encoding\Exception;
use Psl\Regex;
use Psl\Str;
use Psl\Type;

use function base64_decode;

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
function decode(string $base64): string
{
    /** @psalm-suppress MissingThrowsDocblock - pattern is valid */
    if (!Regex\matches($base64, '%^[a-zA-Z0-9/+]*={0,2}$%')) {
        throw new Exception\RangeException(
            'The given base64 string contains characters outside the base64 range.'
        );
    }

    /** @psalm-suppress MissingThrowsDocblock */
    $remainder = Str\length($base64) % 4;
    if (0 !== $remainder) {
        throw new Exception\IncorrectPaddingException(
            'The given base64 string has incorrect padding.'
        );
    }

    /**
     * @psalm-suppress ImpureFunctionCall
     * @psalm-suppress ImpureMethodCall
     * @psalm-suppress MissingThrowsDocblock
     */
    return Type\string()->assert(base64_decode($base64, true));
}
