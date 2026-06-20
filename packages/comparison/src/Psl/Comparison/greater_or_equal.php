<?php

declare(strict_types=1);

namespace Psl\Comparison;

/**
 * @api
 */
function greater_or_equal<T>(T $a, T $b): bool
{
    $order = namespace\compare::<T>($a, $b);

    return $order === Order::Equal || $order === Order::Greater;
}
