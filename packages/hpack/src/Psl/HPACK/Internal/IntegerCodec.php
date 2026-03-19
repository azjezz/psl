<?php

declare(strict_types=1);

namespace Psl\HPACK\Internal;

use Psl\HPACK\Exception\DecodingException;
use Psl\HPACK\Exception\IntegerOverflowException;

use function chr;
use function ord;
use function strlen;

use const PHP_INT_MAX;

/**
 * Variable-length integer encoding/decoding per RFC 7541 Section 5.1.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc7541#section-5.1
 *
 * @internal
 */
final class IntegerCodec
{
    /**
     * @param int $value The integer to encode (must be >= 0).
     * @param int<1, 8> $prefixBits Number of prefix bits (1-8).
     * @param int $prefixByte Existing bits in the first byte (high bits above prefix).
     *
     * @return non-empty-string
     */
    public static function encode(int $value, int $prefixBits, int $prefixByte = 0): string
    {
        $maxPrefix = (1 << $prefixBits) - 1;

        if ($value < $maxPrefix) {
            /** @var non-empty-string */
            return chr($prefixByte | $value);
        }

        $result = chr($prefixByte | $maxPrefix);
        $value -= $maxPrefix;

        while ($value >= 128) {
            $result .= chr(($value & 0x7F) | 0x80);
            $value >>= 7;
        }

        $result .= chr($value);

        /** @var non-empty-string */
        return $result;
    }

    /**
     * @param int<0, max> $offset
     * @param int<1, 8> $prefixBits Number of prefix bits (1-8).
     *
     * @throws DecodingException
     * @throws IntegerOverflowException
     *
     * @return array{int<0, max>, int<0, max>} [decoded value, new offset]
     */
    public static function decode(string $data, int $offset, int $prefixBits): array
    {
        $length = strlen($data);
        if ($offset >= $length) {
            throw DecodingException::forUnexpectedEndOfData();
        }

        $maxPrefix = (1 << $prefixBits) - 1;
        /** @var int<0, max> $value */
        $value = ord($data[$offset]) & $maxPrefix;
        $offset++;

        if ($value < $maxPrefix) {
            return [$value, $offset];
        }

        $shift = 0;
        do {
            if ($offset >= $length) {
                throw DecodingException::forUnexpectedEndOfData();
            }

            $byte = ord($data[$offset]);
            $offset++;

            if ($shift > 56) {
                throw IntegerOverflowException::forValue();
            }

            /** @var int<0, max> $increment */
            $increment = ($byte & 0x7F) << $shift;

            if ($value > (PHP_INT_MAX - $increment)) {
                throw IntegerOverflowException::forValue();
            }

            $value += $increment;
            $shift += 7;
        } while (($byte & 0x80) !== 0);

        /** @var array{int<0, max>, int<0, max>} */
        return [$value, $offset];
    }
}
