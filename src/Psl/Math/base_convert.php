<?php

declare(strict_types=1);

namespace Psl\Math;

use function bcadd;
use function bccomp;
use function bcdiv;
use function bcmod;
use function bcmul;
use function bcpow;
use Psl;
use Psl\Str;
use Psl\Str\Byte;

/**
 * Converts the given string in base `$from_base` to base `$to_base`, assuming
 * letters a-z are used for digits for bases greater than 10. The conversion is
 * done to arbitrary precision.
 *
 * Example:
 *
 *      Math\base_convert('10', 2, 10)
 *      => Str(2)
 *
 *      Math\base_convert('5497', 10, 2)
 *      => Str('1010101111001')
 *
 *      Math\base_convert('2014587925987', 10, 36)
 *      => Str('pphlmw9v')
 *
 * @psalm-pure
 *
 * @throws Psl\Exception\InvariantViolationException If $value is empty, $from_base and $to_base are out of the [2, 36] range, or the given value is invalid.
 */
function base_convert(string $value, int $from_base, int $to_base): string
{
    Psl\invariant('' !== $value, 'Unexpected empty string, expected number in base %d', $from_base);
    Psl\invariant($from_base >= 2 && $from_base <= 36, 'Expected $from_base to be between 2 and 36, got %d', $from_base);
    Psl\invariant($to_base >= 2 && $to_base <= 36, 'Expected $to_base to be between 2 and 36, got %d', $to_base);

    $from_alphabet = Byte\slice(Str\ALPHABET_ALPHANUMERIC, 0, $from_base);
    /** @var numeric-string $result_decimal */
    $result_decimal = '0';
    /** @var numeric-string $place_value */
    $place_value = bcpow((string)$from_base, (string)(Byte\length($value) - 1));
    /** @var string $digit */
    foreach (Byte\chunk($value) as $digit) {
        $digit_numeric = Byte\search_ci($from_alphabet, $digit);
        Psl\invariant(null !== $digit_numeric, 'Invalid digit %s in base %d', $digit, $from_base);
        $result_decimal = bcadd($result_decimal, bcmul((string)$digit_numeric, $place_value));
        Psl\invariant(is_numeric($result_decimal), 'Unexpected error.');
        /** @var numeric-string $place_value */
        $place_value = bcdiv($place_value, (string)$from_base);
    }

    if (10 === $to_base) {
        return $result_decimal;
    }

    $to_alphabet = Byte\slice(Str\ALPHABET_ALPHANUMERIC, 0, $to_base);
    $result = '';
    do {
        $result = $to_alphabet[(int)bcmod($result_decimal, (string)$to_base)] . $result;
        /** @var numeric-string $result_decimal */
        $result_decimal = bcdiv($result_decimal, (string)$to_base);
    } while (bccomp($result_decimal, '0') > 0);

    return $result;
}
