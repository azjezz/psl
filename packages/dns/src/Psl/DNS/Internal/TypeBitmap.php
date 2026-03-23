<?php

declare(strict_types=1);

namespace Psl\DNS\Internal;

use OutOfBoundsException;
use Psl\DNS\Exception\InvalidArgumentException;
use Psl\DNS\Record\RecordType;

use function chr;
use function ksort;
use function ord;

/**
 * Decodes NSEC/NSEC3 type bitmaps per RFC 4034 Section 4.1.2.
 *
 * @internal
 */
final class TypeBitmap
{
    /**
     * Decode a type bitmap from raw data at the given offset.
     *
     * Each window block consists of:
     * - 1 byte: window number (0-255)
     * - 1 byte: bitmap length (1-32)
     * - N bytes: bitmap
     *
     * Unknown type values are silently skipped.
     *
     * @param non-negative-int $bitmapLength The total number of bytes in the bitmap section.
     *
     * @return list<RecordType>
     *
     * @throws InvalidArgumentException If the type bitmap data is invalid.
     * @throws OutOfBoundsException If the bitmap data is truncated.
     */
    public static function decodeRaw(string $data, int &$offset, int $length, int $bitmapLength): array
    {
        $types = [];
        $bytesRead = 0;

        while ($bytesRead < $bitmapLength) {
            if ($offset >= $length) {
                throw new OutOfBoundsException('Read beyond end of data at offset ' . $offset);
            }

            $windowNumber = ord($data[$offset++]);
            $bytesRead++;

            if ($offset >= $length) {
                throw new OutOfBoundsException('Read beyond end of data at offset ' . $offset);
            }

            $bitmapLen = ord($data[$offset++]);
            $bytesRead++;

            if ($bitmapLen < 1 || $bitmapLen > 32) {
                throw InvalidArgumentException::forTypeBitmapLength($bitmapLen);
            }

            for ($i = 0; $i < $bitmapLen; $i++) {
                if ($offset >= $length) {
                    throw new OutOfBoundsException('Read beyond end of data at offset ' . $offset);
                }

                $byte = ord($data[$offset++]);
                $bytesRead++;

                for ($bit = 0; $bit < 8; $bit++) {
                    if (($byte & (0x80 >> $bit)) === 0) {
                        continue;
                    }

                    $typeValue = ($windowNumber * 256) + ($i * 8) + $bit;
                    $kind = RecordType::tryFrom($typeValue);
                    if ($kind !== null) {
                        $types[] = $kind;
                    }
                }
            }
        }

        return $types;
    }

    /**
     * Encode a list of record kinds into a type bitmap.
     *
     * @param list<RecordType> $types
     */
    public static function encode(array $types): string
    {
        if ($types === []) {
            return '';
        }

        /** @var array<int, array<int, true>> $windows */
        $windows = [];
        foreach ($types as $kind) {
            $window = $kind->value >> 8;
            $bit = $kind->value & 0xFF;
            $windows[$window][$bit] = true;
        }

        ksort($windows);

        $result = '';
        foreach ($windows as $windowNumber => $bits) {
            $maxBit = 0;
            foreach ($bits as $bit => $_) {
                if ($bit <= $maxBit) {
                    continue;
                }

                $maxBit = $bit;
            }

            $bitmapLength = (int) (($maxBit >> 3) + 1);
            $bitmap = '';
            for ($i = 0; $i < $bitmapLength; $i++) {
                $byte = 0;
                for ($bit = 0; $bit < 8; $bit++) {
                    if (!isset($bits[($i * 8) + $bit])) {
                        continue;
                    }

                    $byte |= 0x80 >> $bit;
                }

                $bitmap .= chr($byte);
            }

            $result .= chr($windowNumber) . chr($bitmapLength) . $bitmap;
        }

        return $result;
    }
}
