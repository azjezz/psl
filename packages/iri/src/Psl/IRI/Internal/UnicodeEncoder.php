<?php

declare(strict_types=1);

namespace Psl\IRI\Internal;

use function bin2hex;
use function mb_check_encoding;
use function ord;
use function preg_match;
use function preg_replace_callback;
use function rawurldecode;
use function strlen;
use function strtoupper;

/**
 * IRI/URI byte conversion for Unicode characters.
 *
 * Handles percent-encoding and decoding of non-ASCII UTF-8 bytes for
 * conversion between IRI (Unicode) and URI (ASCII) representations.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3987#section-3.1
 *
 * @internal
 */
final class UnicodeEncoder
{
    /**
     * Percent-encode non-ASCII UTF-8 bytes for URI conversion.
     *
     * Encodes each non-ASCII byte as %HH while preserving ASCII characters
     * and existing valid percent-encoding sequences.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3987#section-3.1
     */
    public static function encodeToURI(string $input): string
    {
        $result = '';
        $length = strlen($input);

        for ($i = 0; $i < $length; $i++) {
            $byte = ord($input[$i]);

            if ($byte < 0x80) {
                $result .= $input[$i];
                continue;
            }

            $result .= '%' . strtoupper(bin2hex($input[$i]));
        }

        return $result;
    }

    /**
     * Decode percent-encoded UTF-8 sequences back to Unicode characters.
     *
     * Only decodes sequences that form valid UTF-8 code points beyond ASCII.
     * Preserves percent-encoding of ASCII characters (e.g. %20 stays as %20).
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3987#section-3.1
     */
    public static function decodeFromURI(string $input): string
    {
        return preg_replace_callback(
            '/((?:%[0-9A-Fa-f]{2})+)/',
            static function (array $matches): string {
                $decoded = rawurldecode($matches[0]);

                if (preg_match('/[\x80-\xFF]/', $decoded) && mb_check_encoding($decoded, 'UTF-8')) {
                    return $decoded;
                }

                return strtoupper($matches[0]);
            },
            $input,
        ) ?? $input;
    }
}
