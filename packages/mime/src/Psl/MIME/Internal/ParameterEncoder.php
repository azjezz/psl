<?php

declare(strict_types=1);

namespace Psl\MIME\Internal;

use function dechex;
use function ord;
use function str_pad;
use function strlen;
use function substr;

use const STR_PAD_LEFT;

/**
 * RFC 2231 parameter value encoder.
 *
 * Encodes parameter values that contain non-ASCII characters or characters
 * that cannot appear in token or quoted-string values. Supports multi-section
 * continuation for long values.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2231
 *
 * @internal
 */
final class ParameterEncoder
{
    /**
     * Maximum byte length for a single RFC 2231 continuation section value.
     */
    private const int MAX_SECTION_LENGTH = 40;

    /**
     * Encode a parameter name/value pair, using RFC 2231 encoding if the value
     * contains non-ASCII characters, and splitting into continuations if needed.
     *
     * Returns one or more name/value pairs ready for serialization into a media type string.
     *
     * @return list<array{string, string}>
     */
    public static function encode(string $name, string $value): array
    {
        if (self::isAsciiSafe($value)) {
            return [[$name, $value]];
        }

        $encoded = self::percentEncode($value);
        $prefix = "utf-8''";
        $full = $prefix . $encoded;

        if ((strlen($name) + 1 + strlen($full)) <= 78) {
            return [[$name . '*', $full]];
        }

        return self::splitContinuations($name, $prefix, $encoded);
    }

    /**
     * Split an encoded value into numbered RFC 2231 continuation sections.
     *
     * Each section respects {@see self::MAX_SECTION_LENGTH} and avoids breaking
     * percent-encoded triplets across section boundaries.
     *
     * @return list<array{string, string}>
     */
    private static function splitContinuations(string $name, string $prefix, string $encoded): array
    {
        $parts = [];
        $section = 0;
        $offset = 0;
        $encodedLen = strlen($encoded);

        while ($offset < $encodedLen) {
            $sectionName = $name . '*' . $section . '*';

            if ($section === 0) {
                $available = self::MAX_SECTION_LENGTH - strlen($prefix);
            } else {
                $available = self::MAX_SECTION_LENGTH;
            }

            $chunk = self::sliceEncodedSafe($encoded, $offset, $available);
            $chunkLen = strlen($chunk);

            if ($section === 0) {
                $parts[] = [$sectionName, $prefix . $chunk];
            } else {
                $parts[] = [$sectionName, $chunk];
            }

            $offset += $chunkLen;
            $section++;
        }

        return $parts;
    }

    /**
     * Slice an encoded string without breaking percent-encoded sequences.
     */
    private static function sliceEncodedSafe(string $encoded, int $offset, int $maxLength): string
    {
        /** @var non-negative-int $maxLength */
        $available = substr($encoded, $offset, $maxLength);
        $len = strlen($available);

        if ($len >= 1 && substr($available, $len - 1, 1) === '%') {
            $available = substr($available, 0, $len - 1);
        } elseif ($len >= 2 && substr($available, $len - 2, 1) === '%') {
            $available = substr($available, 0, $len - 2);
        }

        return $available;
    }

    /**
     * Check if a value only contains ASCII-safe characters that don't need encoding.
     */
    private static function isAsciiSafe(string $value): bool
    {
        for ($i = 0, $len = strlen($value); $i < $len; $i++) {
            $ord = ord(substr($value, $i, 1));
            if ($ord > 126 || $ord < 32) {
                return false;
            }
        }

        return true;
    }

    /**
     * Percent-encode a value per RFC 2231.
     *
     * Encodes all characters except unreserved characters (ALPHA, DIGIT, and a few symbols).
     */
    private static function percentEncode(string $value): string
    {
        $result = '';
        for ($i = 0, $len = strlen($value); $i < $len; $i++) {
            $char = substr($value, $i, 1);
            $ord = ord($char);

            $safe =
                $ord >= 0x41 && $ord <= 0x5A || // A-Z
                $ord >= 0x61 && $ord <= 0x7A || // a-z
                $ord >= 0x30 && $ord <= 0x39 || // 0-9
                $char === '-'
                || $char === '.'
                || $char === '_'
                || $char === '~';

            if ($safe) {
                $result .= $char;
            } else {
                $result .= '%' . str_pad(dechex($ord), 2, '0', STR_PAD_LEFT);
            }
        }

        return $result;
    }
}
