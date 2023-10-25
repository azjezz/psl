<?php

declare(strict_types=1);

namespace Psl\Comparison;

/**
 * @template T
 *
 * @param T $a
 * @param T $b
 */
function less_or_equal(mixed $a, mixed $b): bool
{
    $order = compare($a, $b);

    return $order === Order::Equal || $order === Order::Less;
}
