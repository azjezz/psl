<?php

declare(strict_types=1);

namespace Psl\MIME\Internal\CMS;

use Psl\MIME\Exception\CMSException;

use function dechex;
use function ord;
use function strlen;
use function substr;

/**
 * ASN.1 DER (Distinguished Encoding Rules) decoding for CMS structures.
 *
 * Parses raw DER byte strings into tag/content pairs. Supports definite-length
 * encodings up to 4 length-of-length octets (lengths up to 2^32 - 1).
 *
 * @link https://www.itu.int/rec/T-REC-X.690 ITU-T X.690 (BER/CER/DER encoding rules)
 *
 * @internal
 */
final class DERDecoder
{
    /**
     * Parse a single DER TLV (tag-length-value) element from the beginning of the data.
     *
     * Returns the tag byte, the content bytes, and any remaining unparsed bytes.
     *
     * @return array{int, string, string} [tag, content, remainder]
     *
     * @throws CMSException If the data is truncated or uses an unsupported length encoding.
     */
    public static function parse(string $data): array
    {
        $dataLen = strlen($data);
        if ($dataLen < 2) {
            throw CMSException::forMalformedStructure('DER data too short');
        }

        $tag = ord($data[0]);
        $lengthByte = ord($data[1]);
        $offset = 2;

        if ($lengthByte < 128) {
            $length = $lengthByte;
        } elseif ($lengthByte === 0x81) {
            if ($dataLen < 3) {
                throw CMSException::forMalformedStructure('DER length truncated');
            }

            $length = ord($data[2]);
            $offset = 3;
        } elseif ($lengthByte === 0x82) {
            if ($dataLen < 4) {
                throw CMSException::forMalformedStructure('DER length truncated');
            }

            $length = (ord($data[2]) << 8) | ord($data[3]);
            $offset = 4;
        } elseif ($lengthByte === 0x83) {
            if ($dataLen < 5) {
                throw CMSException::forMalformedStructure('DER length truncated');
            }

            $length = (ord($data[2]) << 16) | (ord($data[3]) << 8) | ord($data[4]);
            $offset = 5;
        } elseif ($lengthByte === 0x84) {
            if ($dataLen < 6) {
                throw CMSException::forMalformedStructure('DER length truncated');
            }

            $length = (ord($data[2]) << 24) | (ord($data[3]) << 16) | (ord($data[4]) << 8) | ord($data[5]);
            $offset = 6;
        } else {
            throw CMSException::forMalformedStructure('unsupported DER length encoding');
        }

        if (($offset + $length) > $dataLen) {
            throw CMSException::forMalformedStructure('DER content exceeds data bounds');
        }

        /** @var non-negative-int $length */
        $content = substr($data, $offset, $length);
        $remainder = substr($data, $offset + $length);

        return [$tag, $content, $remainder];
    }

    /**
     * Parse all consecutive DER TLV elements in the data until no bytes remain.
     *
     * @return list<array{int, string}> List of [tag, content] pairs.
     *
     * @throws CMSException If any element is malformed.
     */
    public static function parseAll(string $data): array
    {
        $elements = [];
        while ($data !== '') {
            [$tag, $content, $data] = self::parse($data);
            $elements[] = [$tag, $content];
        }

        return $elements;
    }

    /**
     * Unwrap a SEQUENCE (tag 0x30) and return its inner content bytes.
     *
     * Convenience method for structures where a SEQUENCE is expected at the top level.
     *
     * @throws CMSException If the outermost tag is not 0x30 (SEQUENCE).
     */
    public static function parseSequence(string $data): string
    {
        [$tag, $content] = self::parse($data);
        if ($tag !== 0x30) {
            /** @var non-negative-int $tag - guaranteed positive from \ord */
            throw CMSException::forMalformedStructure('expected SEQUENCE, got tag 0x' . dechex($tag));
        }

        return $content;
    }
}
