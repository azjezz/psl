<?php

declare(strict_types=1);

namespace Psl\Encoding\EncodedWord\Internal;

use Psl\Encoding\EncodedWord;
use Psl\Encoding\Exception;

use function base64_decode;
use function strtoupper;

/**
 * Decode the payload of an RFC 2047 encoded-word.
 *
 * @internal
 *
 * @throws Exception\ParsingException If the encoding is unsupported or decoding fails.
 */
function decode_payload(string $encoding, string $text): string
{
    $encoding = strtoupper($encoding);

    if ($encoding === EncodedWord\B_ENCODING) {
        $decoded = base64_decode($text, true);
        if ($decoded === false) {
            throw Exception\ParsingException::forInvalidEncodedWord($text);
        }

        return $decoded;
    }

    if ($encoding === EncodedWord\Q_ENCODING) {
        return namespace\q_decode($text);
    }

    // @codeCoverageIgnoreStart
    throw Exception\ParsingException::forInvalidEncodedWord($text);
    // @codeCoverageIgnoreEnd
}
