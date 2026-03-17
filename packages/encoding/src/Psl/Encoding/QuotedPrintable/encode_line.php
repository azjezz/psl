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
 * to keep lines within $maxLineLength (default 76 per RFC 2045).
 *
 * @param positive-int $maxLineLength
 * @param non-empty-string $lineEnding
 */
function encode_line(string $line, int $maxLineLength = 76, string $lineEnding = "\r\n"): string
{
    if ($line === '') {
        // @codeCoverageIgnoreStart
        return '';
        // @codeCoverageIgnoreEnd
    }

    $softBreak = '=' . $lineEnding;
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

        if (($lineLength + $charLen) > ($maxLineLength - 1)) {
            $encoded .= $softBreak;
            $lineLength = 0;
        }

        $encoded .= $char;
        $lineLength += $charLen;
    }

    return $encoded;
}
