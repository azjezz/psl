<?php

declare(strict_types=1);

namespace Psl\Encoding\Base64\Internal;

use Psl\Encoding\Exception;
use Psl\Regex;
use Psl\Str;

use function pack;
use function unpack;

/**
 * The following class was derived from code of Paragon Initiative Enterprises.
 *
 * https://github.com/paragonie/constant_time_encoding/blob/198317fa6db951dd791be0740915dae878f34b3a/src/Base64.php
 *
 * Code subject to MIT License (https://github.com/paragonie/constant_time_encoding/blob/198317fa6db951dd791be0740915dae878f34b3a/LICENSE.txt)
 *
 * Copyright (c) 2016 - 2022 Paragon Initiative Enterprises
 *
 * @internal
 */
abstract class Base64
{
    /**
     * Convert a binary string into a base64-encoded string.
     *
     * Base64 character set:
     *  [A-Z]      [a-z]      [0-9]      +     /
     *  0x41-0x5a, 0x61-0x7a, 0x30-0x39, 0x2b, 0x2f
     *
     * @pure
     */
    public static function encode(string $binary, bool $padding = true): string
    {
        $dest = '';
        $binary_length = Str\length($binary, encoding: Str\Encoding::ASCII_8BIT);

        for ($i = 0; $i + 3 <= $binary_length; $i += 3) {
            /** @var array<int, int> $chunk */
            $chunk = unpack('C*', Str\slice($binary, $i, 3, encoding: Str\Encoding::ASCII_8BIT));
            $byte0 = $chunk[1];
            $byte1 = $chunk[2];
            $byte2 = $chunk[3];
            $dest .= static::encode6Bits($byte0 >> 2) .
                static::encode6Bits((($byte0 << 4) | ($byte1 >> 4)) & 63) .
                static::encode6Bits((($byte1 << 2) | ($byte2 >> 6)) & 63) .
                static::encode6Bits($byte2 & 63);
        }
        if ($i < $binary_length) {
            /**
             * @psalm-suppress InvalidArgument
             *
             * @var array<int, int> $chunk
             */
            $chunk = unpack('C*', Str\slice($binary, $i, $binary_length - $i, encoding: Str\Encoding::ASCII_8BIT));
            $byte0 = $chunk[1];
            if ($i + 1 < $binary_length) {
                $byte1 = $chunk[2];
                $dest .= static::encode6Bits($byte0 >> 2) .
                    static::encode6Bits((($byte0 << 4) | ($byte1 >> 4)) & 63) .
                    static::encode6Bits(($byte1 << 2) & 63);
                if ($padding) {
                    $dest .= '=';
                }
            } else {
                $dest .= static::encode6Bits($byte0 >> 2) .
                    static::encode6Bits(($byte0 << 4) & 63);
                if ($padding) {
                    $dest .= '==';
                }
            }
        }

        return $dest;
    }

    /**
     * Decode a base64-encoded string into raw binary.
     *
     * Base64 character set:
     *  [A-Z]      [a-z]      [0-9]      +     /
     *  0x41-0x5a, 0x61-0x7a, 0x30-0x39, 0x2b, 0x2f
     *
     * @pure
     *
     * @throws Exception\RangeException If the encoded string contains characters outside
     *                                  the base64 characters range.
     * @throws Exception\IncorrectPaddingException If the encoded string has an incorrect padding.
     */
    public static function decode(string $base64, bool $explicit_padding = true): string
    {
        $base64_length = Str\length($base64, encoding: Str\Encoding::ASCII_8BIT);
        if ($base64_length === 0) {
            return '';
        }

        static::checkRange($base64);

        if ($explicit_padding && $base64_length % 4 !== 0) {
            throw new Exception\IncorrectPaddingException('The given base64 string has incorrect padding.');
        }

        /** @psalm-suppress MissingThrowsDocblock */
        $base64 = Str\trim_right($base64, '=');
        $base64_length = Str\length($base64, encoding: Str\Encoding::ASCII_8BIT);

        $err = 0;
        $dest = '';
        for ($i = 0; $i + 4 <= $base64_length; $i += 4) {
            /** @var array<int, int> $chunk */
            $chunk = unpack('C*', Str\slice($base64, $i, 4, encoding: Str\Encoding::ASCII_8BIT));
            $char0 = static::decode6Bits($chunk[1]);
            $char1 = static::decode6Bits($chunk[2]);
            $char2 = static::decode6Bits($chunk[3]);
            $char3 = static::decode6Bits($chunk[4]);
            $dest .= pack(
                'CCC',
                ((($char0 << 2) | ($char1 >> 4)) & 0xff),
                ((($char1 << 4) | ($char2 >> 2)) & 0xff),
                ((($char2 << 6) | $char3) & 0xff),
            );
            $err |= ($char0 | $char1 | $char2 | $char3) >> 8;
        }
        if ($i < $base64_length) {
            /**
             * @psalm-suppress InvalidArgument
             *
             * @var array<int, int> $chunk
             */
            $chunk = unpack('C*', Str\slice($base64, $i, $base64_length - $i, encoding: Str\Encoding::ASCII_8BIT));
            $char0 = static::decode6Bits($chunk[1]);
            if ($i + 2 < $base64_length) {
                $char1 = static::decode6Bits($chunk[2]);
                $char2 = static::decode6Bits($chunk[3]);
                $dest .= pack(
                    'CC',
                    ((($char0 << 2) | ($char1 >> 4)) & 0xff),
                    ((($char1 << 4) | ($char2 >> 2)) & 0xff),
                );
                $err |= ($char0 | $char1 | $char2) >> 8;
            } elseif ($i + 1 < $base64_length) {
                $char1 = static::decode6Bits($chunk[2]);
                $dest .= pack('C', ((($char0 << 2) | ($char1 >> 4)) & 0xff));
                $err |= ($char0 | $char1) >> 8;
            } elseif ($explicit_padding) {
                $err |= 1;
            }
        }
        $check = ($err === 0);
        if (!$check) {
            throw new Exception\RangeException(
                'Expected characters in the correct base64 alphabet',
            );
        }

        return $dest;
    }

    /**
     * @throws Exception\RangeException If the encoded string contains characters outside
     *                                  the base64 characters range.
     *
     * @pure
     */
    protected static function checkRange(string $base64): void
    {
        /** @psalm-suppress MissingThrowsDocblock - pattern is valid */
        if (!Regex\matches($base64, '%^[a-zA-Z0-9/+]*={0,2}$%')) {
            throw new Exception\RangeException(
                'The given base64 string contains characters outside the base64 range.'
            );
        }
    }

    /**
     * Uses bitwise operators instead of table-lookups to turn 8-bit integers
     * into 6-bit integers.
     *
     * @pure
     */
    protected static function encode6Bits(int $bin): string
    {
        $diff = 0x41;
        // if ($bin > 25) $diff += 0x61 - 0x41 - 26; // 6
        $diff += ((25 - $bin) >> 8) & 6;
        // if ($bin > 51) $diff += 0x30 - 0x61 - 26; // -75
        $diff -= ((51 - $bin) >> 8) & 75;
        // if ($bin > 61) $diff += 0x2b - 0x30 - 10; // -15
        $diff -= ((61 - $bin) >> 8) & 15;
        // if ($bin > 62) $diff += 0x2f - 0x2b - 1; // 3
        $diff += ((62 - $bin) >> 8) & 3;

        return pack('C', $bin + $diff);
    }

    /**
     * Uses bitwise operators instead of table-lookups to turn 6-bit integers
     * into 8-bit integers.
     *
     * @pure
     */
    protected static function decode6Bits(int $base64): int
    {
        $ret = -1;
        // if ($base64 > 0x40 && $base64 < 0x5b) $ret += $base64 - 0x41 + 1; // -64
        $ret += (((0x40 - $base64) & ($base64 - 0x5b)) >> 8) & ($base64 - 64);
        // if ($base64 > 0x60 && $base64 < 0x7b) $ret += $base64 - 0x61 + 26 + 1; // -70
        $ret += (((0x60 - $base64) & ($base64 - 0x7b)) >> 8) & ($base64 - 70);
        // if ($base64 > 0x2f && $base64 < 0x3a) $ret += $base64 - 0x30 + 52 + 1; // 5
        $ret += (((0x2f - $base64) & ($base64 - 0x3a)) >> 8) & ($base64 + 5);
        // if ($base64 == 0x2b) $ret += 62 + 1;
        $ret += (((0x2a - $base64) & ($base64 - 0x2c)) >> 8) & 63;
        // if ($base64 == 0x2f) ret += 63 + 1;
        $ret += (((0x2e - $base64) & ($base64 - 0x30)) >> 8) & 64;

        return $ret;
    }
}
