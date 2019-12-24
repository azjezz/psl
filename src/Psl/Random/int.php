<?php

declare(strict_types=1);

namespace Psl\Random;

use Psl;
use Psl\Math;

/**
 * Returns a cryptographically secure random integer in the range in the given range.
 */
function int(int $min = Math\INT64_MIN, int $max = Math\INT64_MAX): int
{
    Psl\invariant($min <= $max, 'Expected $min (%d) to be less than or equal to $max (%d).', $min, $max);

    return \random_int($min, $max);
}
