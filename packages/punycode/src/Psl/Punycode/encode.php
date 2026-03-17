<?php

declare(strict_types=1);

namespace Psl\Punycode;

use Psl\Punycode\Internal\Codec;

/**
 * Encode a Unicode string to Punycode per RFC 3492.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3492#section-6.3
 *
 * @throws Exception\EncodingException If encoding overflows or the input is invalid.
 */
function encode(string $input): string
{
    return Codec::encode($input);
}
