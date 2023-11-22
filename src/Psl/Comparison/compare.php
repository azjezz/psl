<?php

declare(strict_types=1);

namespace Psl\Comparison;

/**
 * @template T
 *
 * @param T $a
 * @param T $b
 *
 * This function can compare 2 values of a similar type.
 * When the type happens to be mixed or never, it will fall back to PHP's internal comparison rules:
 *
 * @link https://www.php.net/manual/en/language.operators.comparison.php
 * @link https://www.php.net/manual/en/types.comparisons.php
 */
function compare(mixed $a, mixed $b): Order
{
    if ($a instanceof Comparable) {
        return $a->compare($b);
    }

    return Order::from($a <=> $b);
}
