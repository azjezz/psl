<?php

declare(strict_types=1);

namespace Psl\Encoding\EncodedWord\Internal;

use function implode;
use function strlen;

/**
 * Encode text as Q-encoded words per RFC 2047.
 *
 * @internal
 */
function encode_q_words(string $text, string $prefix, string $suffix, int $maxPayload): string
{
    $words = [];
    $currentWord = '';
    $currentLen = 0;
    $len = strlen($text);

    for ($i = 0; $i < $len; $i++) {
        $byte = $text[$i];
        $encoded = namespace\q_encode_byte($byte);
        $encodedLen = strlen($encoded);

        // @codeCoverageIgnoreStart
        if (($currentLen + $encodedLen) > $maxPayload && $currentWord !== '') {
            $words[] = $prefix . $currentWord . $suffix;
            $currentWord = '';
            $currentLen = 0;
        }

        // @codeCoverageIgnoreEnd

        $currentWord .= $encoded;
        $currentLen += $encodedLen;
    }

    if ($currentWord !== '') {
        $words[] = $prefix . $currentWord . $suffix;
    }

    return implode("\r\n ", $words);
}
