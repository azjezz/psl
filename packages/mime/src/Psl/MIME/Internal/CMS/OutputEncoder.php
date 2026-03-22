<?php

declare(strict_types=1);

namespace Psl\MIME\Internal\CMS;

use Psl\MIME\SMIME\Encoding;

use function base64_decode;
use function base64_encode;
use function chunk_split;
use function preg_replace;
use function strpos;
use function substr;

/**
 * Encodes and decodes CMS structures between DER, PEM, and S/MIME formats.
 *
 * Supports three output formats: raw DER bytes, PEM (base64 with BEGIN/END markers),
 * and S/MIME (MIME headers with base64 body per RFC 8551).
 *
 * @link https://datatracker.ietf.org/doc/html/rfc8551
 *
 * @internal
 */
final class OutputEncoder
{
    /**
     * Encode DER data into the requested format.
     *
     * @param string $der Raw DER-encoded CMS structure.
     * @param Encoding $encoding Target encoding format.
     * @param string $smimeType The S/MIME type for SMIME encoding (e.g., 'signed-data', 'enveloped-data').
     */
    public static function encode(string $der, Encoding $encoding, string $smimeType): string
    {
        return match ($encoding) {
            Encoding::DER => $der,
            Encoding::PEM => DEREncoder::pemEncode($der, 'CMS'),
            Encoding::SMIME => self::smimeEncode($der, $smimeType),
        };
    }

    /**
     * Decode from the given encoding format back to DER.
     *
     * @param string $data Encoded CMS data.
     * @param Encoding $encoding The encoding format of the input.
     *
     */
    public static function decode(string $data, Encoding $encoding): string
    {
        return match ($encoding) {
            Encoding::DER => $data,
            Encoding::PEM => DEREncoder::pemDecode($data),
            Encoding::SMIME => self::smimeDecode($data),
        };
    }

    /**
     * Encode DER data as an S/MIME entity with appropriate MIME headers.
     *
     * Produces a complete MIME entity with Content-Type application/pkcs7-mime,
     * Content-Transfer-Encoding base64, and Content-Disposition attachment headers.
     *
     * @param string $der Raw DER-encoded CMS structure.
     * @param string $smimeType The S/MIME type value (e.g. "signed-data", "enveloped-data").
     */
    private static function smimeEncode(string $der, string $smimeType): string
    {
        $base64 = chunk_split(base64_encode($der), 76, "\r\n");

        return (
            "MIME-Version: 1.0\r\n"
            . "Content-Disposition: attachment; filename=\"smime.p7m\"\r\n"
            . "Content-Type: application/pkcs7-mime; smime-type={$smimeType}; name=\"smime.p7m\"\r\n"
            . "Content-Transfer-Encoding: base64\r\n"
            . "\r\n"
            . $base64
        );
    }

    /**
     * Decode an S/MIME entity back to raw DER bytes.
     *
     * Strips MIME headers (separated by a blank line) and base64-decodes the body.
     * Falls back to PEM decoding if no header/body separator is found.
     */
    private static function smimeDecode(string $data): string
    {
        $pos = strpos($data, "\r\n\r\n");
        if ($pos === false) {
            $pos = strpos($data, "\n\n");
            if ($pos === false) {
                return DEREncoder::pemDecode($data);
            }

            $body = substr($data, $pos + 2);
        } else {
            $body = substr($data, $pos + 4);
        }

        $body = preg_replace('/\s+/', '', $body) ?? $body;

        $decoded = base64_decode($body, true);

        return $decoded !== false ? $decoded : '';
    }
}
