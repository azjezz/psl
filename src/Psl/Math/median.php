<?php

declare(strict_types=1);

namespace Psl\Math;

use Psl\Arr;
use Psl\Iter;

/**
 * Returns the median of the given numbers.
 *
 * @pslam-param iterable<int|float> $numbers
 */
function median(iterable $numbers): ?float
{
    /** @psalm-var list<int|float> $numbers */
    $numbers = Iter\to_array($numbers);
    /** @psalm-var list<int|float> $numbers */
    $numbers = Arr\sort($numbers);
    $count   = Arr\count($numbers);
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
