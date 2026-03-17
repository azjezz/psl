<?php

declare(strict_types=1);

namespace Psl\Math;

use function bcadd;
use function bccomp;
use function bcdiv;
use function bcmod;
use function bcmul;
use function bcpow;
use function sprintf;
use function str_split;
use function stripos;
use function strlen;
use function substr;

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
    $alphanumeric = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $fromAlphabet = substr($alphanumeric, 0, $fromBase);
    $resultDecimal = '0';
    $placeValue = bcpow((string) $fromBase, (string) (strlen($value) - 1));
    foreach (str_split($value) as $digit) {
        $digitNumeric = stripos($fromAlphabet, $digit);
        if (false === $digitNumeric) {
            throw new Exception\InvalidArgumentException(sprintf('Invalid digit %s in base %d', $digit, $fromBase));
        }

        $resultDecimal = bcadd($resultDecimal, bcmul((string) $digitNumeric, $placeValue));
        $placeValue = bcdiv($placeValue, (string) $fromBase);
    }

    if (10 === $toBase) {
        return $resultDecimal;
    }

    $toAlphabet = substr($alphanumeric, 0, $toBase);
    $result = '';
    do {
        $result = $toAlphabet[(int) bcmod($resultDecimal, (string) $toBase)] . $result;
        $resultDecimal = bcdiv($resultDecimal, (string) $toBase);
    } while (bccomp($resultDecimal, '0') > 0);

    return $result;
}
