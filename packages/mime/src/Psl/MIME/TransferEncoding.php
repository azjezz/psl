<?php

declare(strict_types=1);

namespace Psl\MIME;

use function max;
use function ord;
use function strlen;
use function substr;

/**
 * Content-Transfer-Encoding values per RFC 2045 §6.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2045#section-6
 *
 * @api
 */
enum TransferEncoding: string
{
    /**
     * Base64 encoding; converts arbitrary binary data to ASCII using a 64-character alphabet.
     */
    case Base64 = 'base64';

    /**
     * Quoted-Printable encoding; encodes high bytes as "=XX" while leaving most ASCII printable characters intact.
     */
    case QuotedPrintable = 'quoted-printable';

    /**
     * 7-bit encoding; content consists only of US-ASCII characters with lines no longer than 998 octets.
     */
    case SevenBit = '7bit';

    /**
     * 8-bit encoding; content may include non-ASCII octets but lines are no longer than 998 octets.
     */
    case EightBit = '8bit';

    /**
     * Binary encoding; content may include any octets with no line length restriction.
     */
    case Binary = 'binary';

    /**
     * Auto-detect the best encoding for the given content.
     *
     * - If all bytes are 7-bit ASCII with no long lines: 7bit
     * - If mostly printable with some high bytes: quoted-printable
     * - Otherwise: base64
     */
    public static function detect(string $content): self
    {
        if ($content === '') {
            return self::SevenBit;
        }

        $length = strlen($content);
        $highBytes = 0;
        $controlBytes = 0;
        $maxLineLength = 0;
        $currentLineLength = 0;

        for ($i = 0; $i < $length; $i++) {
            $byte = ord(substr($content, $i, 1));

            if ($byte === 0x0A) {
                $maxLineLength = max($maxLineLength, $currentLineLength);
                $currentLineLength = 0;
                continue;
            }

            if ($byte === 0x0D) {
                continue;
            }

            $currentLineLength++;

            if ($byte > 127) {
                $highBytes++;
            } elseif ($byte < 32 && $byte !== 0x09) {
                $controlBytes++;
            }
        }

        $maxLineLength = max($maxLineLength, $currentLineLength);

        if ($controlBytes > 0) {
            return self::Base64;
        }

        if ($highBytes === 0 && $maxLineLength <= 998) {
            return self::SevenBit;
        }

        if ($highBytes > 0 && $maxLineLength <= 998) {
            $ratio = $highBytes / $length;
            if ($ratio > 0.3) {
                return self::Base64;
            }

            return self::QuotedPrintable;
        }

        if ($highBytes === 0 && $maxLineLength > 998) {
            return self::QuotedPrintable;
        }

        return self::Base64;
    }
}
