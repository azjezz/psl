<?php

declare(strict_types=1);

namespace Psl\Math;

use function exp as php_exp;

/**
 * Returns Math\E to the power of the given number.
 *
 *  Example:
 *
 *      Math\exp(12)
 *      => Float(162754.79141900392)
 *
 *      Math\exp(5.7)
 *      => Float(298.8674009670603)
 *
 * @pure
 */
function exp(float $num): float
{
    return php_exp($num);
}
