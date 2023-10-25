<?php

declare(strict_types=1);

namespace Psl\Comparison;

/**
 * @template T
 *
 * @param T $a
 * @param T $b
 *
 * This method can be used as a sorter callback function for Comparable items.
 *
 * Vec\sort($list, Comparable\sort(...))
 */
function sort($a, $b): int
{
    return compare($a, $b)->value;
}
