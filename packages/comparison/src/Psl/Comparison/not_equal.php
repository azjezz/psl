<?php

declare(strict_types=1);

namespace Psl\Comparison;

/**
 * @param T $a
 * @param T $b
 *
 * @api
 */
function not_equal<T = mixed>(T $a, T $b): bool
{
    return namespace\compare($a, $b) !== Order::Equal;
}
