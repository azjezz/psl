<?php

declare(strict_types=1);

namespace Psl\URI\Internal;

use function bin2hex;
use function chr;
use function intval;
use function preg_replace_callback;
use function rawurldecode;
use function str_contains;
use function strlen;
use function strtoupper;

/**
 * Percent-encoding utilities per RFC 3986 Section 2.1.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3986#section-2.1
 *
 * @internal
 */
final class PercentEncoder
{
    private const string UNRESERVED = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._~';

    /**
     * Percent-encode a string, leaving only the specified characters unencoded.
     *
     * @param string $input The raw string to encode.
     * @param string $allowed Characters that should NOT be percent-encoded.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3986#section-2.1
     */
    public static function encode(string $input, string $allowed = self::UNRESERVED): string
    {
        $result = '';
        $length = strlen($input);

        for ($i = 0; $i < $length; $i++) {
            $char = $input[$i];
            if (str_contains($allowed, $char)) {
                $result .= $char;
            } else {
                $result .= '%' . strtoupper(bin2hex($char));
            }
        }

        return $result;
    }

    /**
     * Decode all percent-encoded octets in a string.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3986#section-2.1
     */
    public static function decode(string $input): string
    {
        return rawurldecode($input);
    }

    /**
     * Normalize percent-encoding per RFC 3986 Section 2.3 and 2.1.
     *
     * - Decodes percent-encoded unreserved characters (ALPHA, DIGIT, -, ., _, ~)
     * - Uppercases hex digits in remaining percent-encoded octets
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3986#section-2.3
     */
    public static function normalize(string $input): string
    {
        return preg_replace_callback(
            '/%([0-9A-Fa-f]{2})/',
            static function (array $matches): string {
                $hex = $matches[1];
                /** @var non-empty-string $hex */
                $byte = intval($hex, 16);
                $char = chr($byte);

                if (str_contains(self::UNRESERVED, $char)) {
                    return $char;
                }

                return '%' . strtoupper($hex);
            },
            $input,
        )
        ?? $input;
    }
}
