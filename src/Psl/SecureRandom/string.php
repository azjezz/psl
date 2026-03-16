<?php

declare(strict_types=1);

namespace Psl\SecureRandom;

use function ceil;
use function log;
use function strlen;
use function unpack;

/**
 * Returns a securely generated random string of the given length. The string is
 * composed of characters from the given alphabet string.
 *
 * If the alphabet argument is not specified, the returned string will be composed of
 * the alphanumeric characters.
 *
 * @param int<0, max> $length The length of the string to generate.
 *
 * @throws Exception\InsufficientEntropyException If it was not possible to gather sufficient entropy.
 * @throws Exception\InvalidArgumentException If $alphabet length is outside the [2^1, 2^56] range.
 *
 * @psalm-external-mutation-free
 *
 * @return ($length is 0 ? '' : non-empty-string)
 */
function string(int $length, null|string $alphabet = null): string
{
    if (0 === $length) {
        return '';
    }

    $alphabet ??= '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $alphabetSize = strlen($alphabet);
    $bits = (int) ceil(log($alphabetSize, 2.0));
    if ($bits < 1 || $bits > 56) {
        throw new Exception\InvalidArgumentException('$alphabet\'s length must be in [2^1, 2^56]');
    }

    $ret = '';
    while ($length > 0) {
        /** @var int<0, max> $urandomLength */
        $urandomLength = (int) ceil((float) (2 * $length * $bits) / 8.0);
        $data = namespace\bytes($urandomLength);

        $unpackedData = 0;
        $unpackedBits = 0;
        for ($i = 0; $i < $urandomLength && $length > 0; ++$i) {
            // Unpack 8 bits
            /** @var array<int, int> $v */
            $v = unpack('C', $data[$i]);
            $unpackedData = ($unpackedData << 8) | $v[1];
            $unpackedBits += 8;

            // While we have enough bits to select a character from the alphabet, keep
            // consuming the random data
            for (; $unpackedBits >= $bits && $length > 0; $unpackedBits -= $bits) {
                $index = $unpackedData & ((1 << $bits) - 1);
                $unpackedData >>= $bits;
                // Unfortunately, the alphabet size is not necessarily a power of two.
                // Worst case, it is 2^k + 1, which means we need (k+1) bits and we
                // have around a 50% chance of missing as k gets larger
                if ($index < $alphabetSize) {
                    $ret .= $alphabet[$index];
                    --$length;
                }
            }
        }
    }

    return $ret;
}
