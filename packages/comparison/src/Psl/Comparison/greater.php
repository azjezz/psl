<?php

declare(strict_types=1);

namespace Psl\Comparison;

/**
 * @param T $a
 * @param T $b
 *
 * @api
 */
function greater<T = mixed>(T $a, T $b): bool
{
    return namespace\compare($a, $b) === Order::Greater;
}
