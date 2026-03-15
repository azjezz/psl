<?php

declare(strict_types=1);

namespace Psl\Str;

use function number_format;

/**
 * Returns a string representation of the given number with grouped thousands.
 *
 * If `$decimals` is provided, the string will contain that many decimal places.
 *
 * The optional `$decimalPoint` and `$thousandsSeparator` arguments define the
 * strings used for decimals and commas, respectively.
 *
 * @pure
 */
function format_number(
    float $number,
    int $decimals = 0,
    string $decimalPoint = '.',
    string $thousandsSeparator = ',',
): string {
    return number_format($number, $decimals, $decimalPoint, $thousandsSeparator);
}
