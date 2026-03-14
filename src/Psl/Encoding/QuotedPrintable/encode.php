<?php

declare(strict_types=1);

namespace Psl\Encoding\QuotedPrintable;

use function explode;
use function implode;
use function str_replace;

/**
 * Encode a string using quoted-printable encoding per RFC 2045 §6.7.
 *
 * @param positive-int $max_line_length Maximum encoded line length (default 76 per RFC 2045).
 * @param non-empty-string $line_ending Line ending sequence (default "\r\n" per RFC 2045).
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2045#section-6.7
 */
function encode(string $data, int $max_line_length = 76, string $line_ending = "\r\n"): string
{
    if ($data === '') {
        return '';
    }

    $data = str_replace("\r\n", "\n", $data);
    $data = str_replace("\r", "\n", $data);

    $lines = explode("\n", $data);
    $result = [];

    foreach ($lines as $line) {
        $result[] = encode_line($line, $max_line_length, $line_ending);
    }

    return implode($line_ending, $result);
}
