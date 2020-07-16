<?php

declare(strict_types=1);

namespace Psl\SecureRandom;

use Psl;
use Psl\Math;
use Psl\Str;

/**
 * Returns a cryptographically secure random integer in the range in the given range.
 *
 * @throws Psl\Exception\InvariantViolationException If $min > $max
 * @throws Psl\Exception\RuntimeException If it was not possible to gather sufficient entropy.
 */
function int(int $min = Math\INT64_MIN, int $max = Math\INT64_MAX): int
{
    Psl\invariant($min <= $max, 'Expected $min (%d) to be less than or equal to $max (%d).', $min, $max);

    try {
        return \random_int($min, $max);
    } catch (\Exception $e) {
        $code = $e->getCode();
        if (Str\is_string($code)) {
            $code = Str\to_int($code) ?? 0;
        }

        throw new Psl\Exception\RuntimeException('Unable to gather sufficient entropy.', $code, $e);
    }
}
