<?php

declare(strict_types=1);

namespace Psl\Comparison;

/**
 * @api
 */
function greater<T>(T $a, T $b): bool
{
    return namespace\compare::<T>($a, $b) === Order::Greater;
}
