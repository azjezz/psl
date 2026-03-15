<?php

declare(strict_types=1);

namespace Psl\Math;

use Psl\Str;
use Psl\Str\Byte;

use function bcadd;
use function bccomp;
use function bcdiv;
use function bcmod;
use function bcmul;
use function bcpow;

/**
 * Converts the given string in base `$fromBase` to base `$toBase`, assuming
 * letters a-z are used for digits for bases greater than 10. The conversion is
 * done to arbitrary precision.
 *
 * @param non-empty-string $value
 * @param int<2, 36> $fromBase
 * @param int<2, 36> $toBase
 *
 * @pure
 *
 * @throws Exception\InvalidArgumentException If the given value is invalid.
 */
function base_convert(string $value, int $fromBase, int $toBase): string
{
    $fromAlphabet = Byte\slice(Str\ALPHABET_ALPHANUMERIC, 0, $fromBase);
    $resultDecimal = '0';
    $placeValue = bcpow((string) $fromBase, (string) (Byte\length($value) - 1));
    foreach (Byte\chunk($value) as $digit) {
        $digitNumeric = Byte\search_ci($fromAlphabet, $digit);
        if (null === $digitNumeric) {
            throw new Exception\InvalidArgumentException(Str\format('Invalid digit %s in base %d', $digit, $fromBase));
        }

        $resultDecimal = bcadd($resultDecimal, bcmul((string) $digitNumeric, $placeValue));
        $placeValue = bcdiv($placeValue, (string) $fromBase);
    }

    if (10 === $toBase) {
        return $resultDecimal;
    }

    $toAlphabet = Byte\slice(Str\ALPHABET_ALPHANUMERIC, 0, $toBase);
    $result = '';
    do {
        $result = $toAlphabet[(int) bcmod($resultDecimal, (string) $toBase)] . $result;
        $resultDecimal = bcdiv($resultDecimal, (string) $toBase);
    } while (bccomp($resultDecimal, '0') > 0);

    return $result;
}
