<?php

declare(strict_types=1);

namespace Psl\Encoding\QuotedPrintable;

use function explode;
use function implode;
use function str_replace;

/**
 * Encode a string using quoted-printable encoding per RFC 2045 §6.7.
 *
 * @param positive-int $maxLineLength Maximum encoded line length (default 76 per RFC 2045).
 * @param non-empty-string $lineEnding Line ending sequence (default "\r\n" per RFC 2045).
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2045#section-6.7
 */
function encode(string $data, int $maxLineLength = 76, string $lineEnding = "\r\n"): string
{
    if ($data === '') {
        return '';
    }

    $data = str_replace("\r\n", "\n", $data);
    $data = str_replace("\r", "\n", $data);

    $lines = explode("\n", $data);
    $result = [];

    foreach ($lines as $line) {
        $result[] = namespace\encode_line($line, $maxLineLength, $lineEnding);
    }

    return implode($lineEnding, $result);
}
