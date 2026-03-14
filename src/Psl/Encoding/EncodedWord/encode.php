<?php

declare(strict_types=1);

namespace Psl\Encoding\EncodedWord;

use Psl\Str\Encoding;

use function strlen;

/**
 * Encode a string as RFC 2047 encoded-words for use in MIME headers.
 *
 * If the text contains only printable ASCII characters (0x20-0x7E), it is returned as-is.
 * Otherwise, Q-encoding or B-encoding is chosen based on the proportion of non-ASCII bytes.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2047
 */
function encode(string $text, Encoding $charset = Encoding::Utf8): string
{
    if ($text === '') {
        return '';
    }

    if (Internal\is_printable_ascii($text)) {
        return $text;
    }

    $encoding = Internal\should_use_b_encoding($text) ? B_ENCODING : Q_ENCODING;

    $prefix = '=?' . $charset->value . '?' . $encoding . '?';
    $suffix = '?=';
    $overhead = strlen($prefix) + strlen($suffix);
    $maxPayload = MAX_ENCODED_WORD_LENGTH - $overhead;

    if ($encoding === B_ENCODING) {
        return Internal\encode_b_words($text, $prefix, $suffix, $maxPayload);
    }

    return Internal\encode_q_words($text, $prefix, $suffix, $maxPayload);
}
