<?php

declare(strict_types=1);

namespace Psl\PseudoRandom;

use function mt_rand;
use function sprintf;

use const PHP_INT_MAX;
use const PHP_INT_MIN;

/**
 * Returns a pseudo-random integer in the given range.
 *
 * @throws Exception\InvalidArgumentException If $min > $max
 *
 * @psalm-external-mutation-free
 */
function int(int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): int
{
    if ($min > $max) {
        throw new Exception\InvalidArgumentException(sprintf(
            'Expected $min (%d) to be less than or equal to $max (%d).',
            $min,
            $max,
        ));
    }

    return mt_rand($min, $max);
}
