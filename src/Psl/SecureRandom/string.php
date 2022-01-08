<?php

declare(strict_types=1);

namespace Psl\SecureRandom;

use Psl\Math;
use Psl\Str;
use Psl\Str\Byte;

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
 */
function string(int $length, ?string $alphabet = null): string
{
    if (0 === $length) {
        return '';
    }

    $alphabet      = $alphabet ?? Str\ALPHABET_ALPHANUMERIC;
    $alphabet_size = Byte\length($alphabet);
    /** @psalm-suppress MissingThrowsDocblock */
    $bits          = (int) Math\ceil(Math\log($alphabet_size, 2.0));
    if ($bits < 1 || $bits > 56) {
        throw new Exception\InvalidArgumentException('$alphabet\'s length must be in [2^1, 2^56]');
    }

    $ret = '';
    while ($length > 0) {
        /** @var int<0, max> $urandom_length */
        $urandom_length = (int) Math\ceil((float) (2 * $length * $bits) / 8.0);
        $data           = namespace\bytes($urandom_length);

        $unpacked_data = 0;
        $unpacked_bits = 0;
        for ($i = 0; $i < $urandom_length && $length > 0; ++$i) {
            // Unpack 8 bits
            /** @var array<int, int> $v */
            $v = unpack('C', $data[$i]);
            $unpacked_data  = ($unpacked_data << 8) | $v[1];
            $unpacked_bits += 8;

            // While we have enough bits to select a character from the alphabet, keep
            // consuming the random data
            for (; $unpacked_bits >= $bits && $length > 0; $unpacked_bits -= $bits) {
                $index           = ($unpacked_data & ((1 << $bits) - 1));
                $unpacked_data >>= $bits;
                // Unfortunately, the alphabet size is not necessarily a power of two.
                // Worst case, it is 2^k + 1, which means we need (k+1) bits and we
                // have around a 50% chance of missing as k gets larger
                if ($index < $alphabet_size) {
                    $ret .= $alphabet[$index];
                    --$length;
                }
            }
        }
    }

    return $ret;
}
