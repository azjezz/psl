<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Return the largest integer value less then or equal to the given number.
 *
 * @param float|int $num
 *
 * @psalm-pure
 */
function floor(float $num): float
{
    return \floor($num);
}
