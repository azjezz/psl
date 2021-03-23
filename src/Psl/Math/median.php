<?php

declare(strict_types=1);

namespace Psl\Math;

use Psl\Iter;
use Psl\Vec;

/**
 * Returns the median of the given numbers.
 *
 * @param iterable<int|float> $numbers
 */
function median(iterable $numbers): ?float
{
    $numbers = Vec\values($numbers);
    $numbers = Vec\sort($numbers);
    $count   = Iter\count($numbers);
    if (0 === $count) {
        return null;
    }

    /** @psalm-suppress MissingThrowsDocblock */
    $middle_index = div($count, 2);
    if (0 === $count % 2) {
        return mean(
            [$numbers[$middle_index], $numbers[$middle_index - 1]]
        ) ?? 0.0;
    }

    return (float) $numbers[$middle_index];
}
