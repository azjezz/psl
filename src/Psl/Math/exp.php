<?php

declare(strict_types=1);

namespace Psl\Math;

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
 * @psalm-pure
 */
function exp(float $num): float
{
    return \exp($num);
}
