<?php

declare(strict_types=1);

namespace Psl\Comparison;

/**
 * @param T $a
 * @param T $b
 *
 * This method can be used as a sorter callback function for Comparable items.
 *
 * Vec\sort($list, Comparable\sort(...))
 *
 * @api
 */
function sort<T>(T $a, T $b): int
{
    return namespace\compare($a, $b)->value;
}
