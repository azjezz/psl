<?php

declare(strict_types=1);

namespace Psl\PseudoRandom;

use Psl;
use Psl\Math;

use function mt_rand;

/**
 * Returns a pseudo-random integer in the given range.
 *
 * @throws Psl\Exception\InvariantViolationException If $min > $max
 *
 * @psalm-external-mutation-free
 */
function int(int $min = Math\INT64_MIN, int $max = Math\INT64_MAX): int
{
    Psl\invariant($min <= $max, 'Expected $min (%d) to be less than or equal to $max (%d).', $min, $max);

    return mt_rand($min, $max);
}
