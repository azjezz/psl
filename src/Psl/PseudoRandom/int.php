<?php

declare(strict_types=1);

namespace Psl\PseudoRandom;

use Psl;
use Psl\Math;

use function mt_rand;

/**
 * Returns a random integer in the range in the given range.
 *
 * @throws Psl\Exception\InvariantViolationException If $min > $max
 */
function int(int $min = Math\INT64_MIN, int $max = Math\INT64_MAX): int
{
    Psl\invariant($min <= $max, 'Expected $min (%d) to be less than or equal to $max (%d).', $min, $max);

    return mt_rand($min, $max);
}
