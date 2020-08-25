<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Returns the sine of the given number.
 *
 * @psalm-pure
 */
function sin(float $num): float
{
    return \sin($num);
}
