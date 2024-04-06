<?php

declare(strict_types=1);

namespace Psl\Math;

use function count;
use function sort;

/**
 * Returns the median of the given numbers in the list.
 *
 * Returns null if the given iterable is empty.
 *
 * @param list<int|float> $numbers
 *
 * @return ($numbers is non-empty-list ? float : null)
 *
 * @pure
 */
function median(array $numbers): float|null
{
    sort($numbers);
    $count   = count($numbers);
    if (0 === $count) {
        return null;
    }

    /** @psalm-suppress MissingThrowsDocblock */
    $middle_index = div($count, 2);
    if (0 === $count % 2) {
        return mean(
            [$numbers[$middle_index], $numbers[$middle_index - 1]]
        );
    }

    return (float) $numbers[$middle_index];
}
