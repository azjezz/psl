<?php

declare(strict_types=1);

namespace Psl\Comparison;

/**
 * @param T $a
 * @param T $b
 *
 * @api
 */
function greater_or_equal<T>(T $a, T $b): bool
{
    $order = namespace\compare($a, $b);

    return $order === Order::Equal || $order === Order::Greater;
}
