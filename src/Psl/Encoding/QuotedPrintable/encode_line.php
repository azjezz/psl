<?php

declare(strict_types=1);

namespace Psl\Encoding\QuotedPrintable;

use function chr;
use function ord;
use function strlen;

/**
 * Encode a single line using quoted-printable encoding.
 *
 * Trailing whitespace is encoded, and soft line breaks are inserted
 * to keep lines within $max_line_length (default 76 per RFC 2045).
 *
 * @param positive-int $max_line_length
 * @param non-empty-string $line_ending
 */
function encode_line(string $line, int $max_line_length = 76, string $line_ending = "\r\n"): string
{
    if ($line === '') {
        return '';
    }

    $soft_break = '=' . $line_ending;
    $encoded = '';
    $lineLength = 0;
    $len = strlen($line);

    for ($i = 0; $i < $len; $i++) {
        $byte = ord($line[$i]);
        $isLast = $i === ($len - 1);

        if ($byte === 0x09 || $byte === 0x20) {
            if ($isLast) {
                $char = Internal\encode_octet($byte);
            } else {
                $char = chr($byte);
            }
        } elseif ($byte >= 33 && $byte <= 126 && $byte !== 61) {
            $char = chr($byte);
        } else {
            $char = Internal\encode_octet($byte);
        }

        $charLen = strlen($char);

        if (($lineLength + $charLen) > ($max_line_length - 1)) {
            $encoded .= $soft_break;
            $lineLength = 0;
        }

        $encoded .= $char;
        $lineLength += $charLen;
    }

    return $encoded;
}
