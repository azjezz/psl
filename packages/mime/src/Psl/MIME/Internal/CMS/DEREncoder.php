<?php

declare(strict_types=1);

namespace Psl\MIME\Internal\CMS;

use function base64_decode;
use function base64_encode;
use function chr;
use function chunk_split;
use function ord;
use function preg_replace;
use function strlen;
use function substr;

/**
 * ASN.1 DER (Distinguished Encoding Rules) encoding primitives for CMS structures.
 *
 * Provides static methods for constructing DER-encoded ASN.1 values as raw byte strings,
 * as well as PEM encoding/decoding utilities.
 *
 * @link https://www.itu.int/rec/T-REC-X.690 ITU-T X.690 (BER/CER/DER encoding rules)
 *
 * @internal
 */
final class DEREncoder
{
    /**
     * Encode a tag-length-value (TLV) triplet per X.690 section 8.1.
     *
     * Supports definite-length encoding for contents up to 2^24 - 1 bytes.
     *
     * @param int $tag The ASN.1 tag byte (e.g. 0x30 for SEQUENCE).
     * @param string $content The already-encoded content (the "value" portion).
     */
    public static function tag(int $tag, string $content): string
    {
        $length = strlen($content);

        if ($length < 128) {
            return chr($tag) . chr($length) . $content;
        }

        if ($length < 256) {
            return chr($tag) . "\x81" . chr($length) . $content;
        }

        if ($length < 65_536) {
            return chr($tag) . "\x82" . chr(($length >> 8) & 0xFF) . chr($length & 0xFF) . $content;
        }

        return (
            chr($tag)
            . "\x83"
            . chr(($length >> 16) & 0xFF)
            . chr(($length >> 8) & 0xFF)
            . chr($length & 0xFF)
            . $content
        );
    }

    /**
     * Encode an ASN.1 INTEGER (tag 0x02) per X.690 section 8.3.
     *
     * Strips leading zero bytes and prepends a 0x00 pad if the high bit is set,
     * ensuring the value is interpreted as non-negative.
     *
     * @param string $bytes The unsigned integer value as big-endian bytes.
     */
    public static function integer(string $bytes): string
    {
        $i = 0;
        $length = strlen($bytes);
        while ($i < ($length - 1) && ord($bytes[$i]) === 0) {
            $i++;
        }

        // @mago-expect analysis:redundant-condition - FP
        if ($i > 0) {
            $bytes = substr($bytes, $i);
        }

        if ((ord($bytes[0]) & 0x80) !== 0) {
            $bytes = "\x00" . $bytes;
        }

        return self::tag(0x02, $bytes);
    }

    /**
     * Encode an ASN.1 SEQUENCE (tag 0x30, constructed) per X.690 section 8.9.
     */
    public static function sequence(string $content): string
    {
        return self::tag(0x30, $content);
    }

    /**
     * Encode an ASN.1 SET (tag 0x31, constructed) per X.690 section 8.11.
     */
    public static function set(string $content): string
    {
        return self::tag(0x31, $content);
    }

    /**
     * Encode an ASN.1 OCTET STRING (tag 0x04) per X.690 section 8.7.
     */
    public static function octetString(string $content): string
    {
        return self::tag(0x04, $content);
    }

    /**
     * Encode an ASN.1 OBJECT IDENTIFIER (tag 0x06) from pre-encoded OID bytes.
     *
     * @param string $oidBytes Raw OID value bytes (e.g. from {@see OID} constants).
     */
    public static function objectIdentifier(string $oidBytes): string
    {
        return self::tag(0x06, $oidBytes);
    }

    /**
     * Encode an ASN.1 BIT STRING (tag 0x03) per X.690 section 8.6.
     *
     * Prepends a 0x00 unused-bits byte, indicating all bits in the content are significant.
     */
    public static function bitString(string $content): string
    {
        return self::tag(0x03, "\x00" . $content);
    }

    /**
     * Encode a context-specific implicit or explicit tag [num] per X.690 section 8.14.
     *
     * @param int $num The context tag number (0-30).
     * @param string $content The encoded content.
     * @param bool $constructed Whether the tag is constructed (true) or primitive (false).
     */
    public static function contextTag(int $num, string $content, bool $constructed = true): string
    {
        $tagByte = 0x80 | $num;
        if ($constructed) {
            $tagByte |= 0x20;
        }

        return self::tag($tagByte, $content);
    }

    /**
     * Encode an ASN.1 PrintableString (tag 0x13) per X.690 section 8.21.
     */
    public static function printableString(string $content): string
    {
        return self::tag(0x13, $content);
    }

    /**
     * Encode an ASN.1 UTF8String (tag 0x0C) per X.690 section 8.21.
     */
    public static function utf8String(string $content): string
    {
        return self::tag(0x0C, $content);
    }

    /**
     * Encode an ASN.1 UTCTime (tag 0x17) per X.690 section 8.23.
     *
     * @param string $content Time string in the format "YYMMDDhhmmssZ".
     */
    public static function utcTime(string $content): string
    {
        return self::tag(0x17, $content);
    }

    /**
     * Encode an ASN.1 NULL value (tag 0x05, length 0x00) per X.690 section 8.8.
     */
    public static function null(): string
    {
        return "\x05\x00";
    }

    /**
     * Encode an ASN.1 BOOLEAN (tag 0x01) per X.690 section 8.2.
     *
     * DER uses 0xFF for true and 0x00 for false.
     */
    public static function boolean(bool $value): string
    {
        return self::tag(0x01, $value ? "\xFF" : "\x00");
    }

    /**
     * PEM-encode DER data with the given label (e.g. "CERTIFICATE", "CMS").
     *
     * Produces base64-encoded output wrapped in BEGIN/END markers per RFC 7468.
     *
     * @param string $der Raw DER-encoded data.
     * @param string $label The PEM label (e.g. "CERTIFICATE", "CMS", "PRIVATE KEY").
     */
    public static function pemEncode(string $der, string $label): string
    {
        $base64 = base64_encode($der);
        $lines = chunk_split($base64, 64, "\n");

        return "-----BEGIN {$label}-----\n" . $lines . "-----END {$label}-----\n";
    }

    /**
     * Decode PEM-encoded data back to raw DER bytes.
     *
     * Strips BEGIN/END markers and base64-decodes the content.
     * Returns an empty string if decoding fails.
     */
    public static function pemDecode(string $pem): string
    {
        $pem = preg_replace('/-----[A-Z0-9 ]+-----/', '', $pem) ?? $pem;
        $pem = preg_replace('/\s+/', '', $pem) ?? $pem;

        $decoded = base64_decode($pem, true);

        return $decoded !== false ? $decoded : '';
    }
}
