<?php

declare(strict_types=1);

namespace Psl\DNS\Internal;

use Psl\DNS\Exception\InvalidArgumentException;

use function chr;
use function ord;
use function strlen;
use function strtoupper;
use function substr;

/**
 * Base32hex codec per RFC 4648 Section 7.
 *
 * Uses the extended hex alphabet (0-9A-V), uppercase, no padding.
 * Used for encoding and decoding NSEC3 hashed owner names.
 *
 * @see https://datatracker.ietf.org/doc/html/rfc4648#section-7
 *
 * @internal
 */
final class Base32Hex
{
    /**
     * The base32hex encoding alphabet (RFC 4648 Section 7).
     */
    private const string ALPHABET = '0123456789ABCDEFGHIJKLMNOPQRSTUV';

    /**
     * Encode binary data to base32hex string.
     */
    public static function encode(string $binary): string
    {
        $length = strlen($binary);
        if ($length === 0) {
            return '';
        }

        $result = '';
        $buffer = 0;
        $bitsLeft = 0;

        for ($i = 0; $i < $length; $i++) {
            $buffer = ($buffer << 8) | ord($binary[$i]);
            $bitsLeft += 8;

            while ($bitsLeft >= 5) {
                $bitsLeft -= 5;
                $index = ($buffer >> $bitsLeft) & 0x1F;
                $result .= substr(self::ALPHABET, $index, 1);
            }
        }

        if ($bitsLeft > 0) {
            $index = ($buffer << (5 - $bitsLeft)) & 0x1F;
            $result .= substr(self::ALPHABET, $index, 1);
        }

        return $result;
    }

    /**
     * Decode a base32hex string to binary data.
     *
     * @throws InvalidArgumentException If an invalid base32hex character is encountered.
     */
    public static function decode(string $encoded): string
    {
        $encoded = strtoupper($encoded);
        $length = strlen($encoded);
        if ($length === 0) {
            return '';
        }

        $result = '';
        $buffer = 0;
        $bitsLeft = 0;

        for ($i = 0; $i < $length; $i++) {
            $char = $encoded[$i];
            if ($char === '=') {
                break;
            }

            $value = self::decodeChar($char);
            $buffer = ($buffer << 5) | $value;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $result .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $result;
    }

    /**
     * Decode a single base32hex character to its integer value.
     *
     * @throws InvalidArgumentException If an invalid base32hex character is encountered.
     */
    private static function decodeChar(string $char): int
    {
        if ($char >= '0' && $char <= '9') {
            return ord($char) - ord('0');
        }

        if ($char >= 'A' && $char <= 'V') {
            return ord($char) - ord('A') + 10;
        }

        throw InvalidArgumentException::forInvalidBase32HexCharacter($char);
    }
}
