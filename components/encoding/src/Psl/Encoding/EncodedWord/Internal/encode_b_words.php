<?php

declare(strict_types=1);

namespace Psl\Encoding\EncodedWord\Internal;

use function base64_encode;
use function floor;
use function implode;
use function strlen;
use function substr;

/**
 * Encode text as B-encoded words per RFC 2047.
 *
 * @internal
 */
function encode_b_words(string $text, string $prefix, string $suffix, int $maxPayload): string
{
    /** @var int<0, max> $maxRaw */
    $maxRaw = (int) ((int) floor($maxPayload / 4) * 3);
    $words = [];
    $offset = 0;
    $len = strlen($text);

    while ($offset < $len) {
        $chunk = substr($text, $offset, $maxRaw);
        $words[] = $prefix . base64_encode($chunk) . $suffix;
        $offset += strlen($chunk);
    }

    return implode("\r\n ", $words);
}
