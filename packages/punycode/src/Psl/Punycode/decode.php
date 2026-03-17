<?php

declare(strict_types=1);

namespace Psl\Punycode;

use Psl\Punycode\Internal\Codec;

/**
 * Decode a Punycode string to Unicode per RFC 3492.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3492#section-6.2
 *
 * @throws Exception\EncodingException If the input is malformed or decoding overflows.
 */
function decode(string $input): string
{
    return Codec::decode($input);
}
