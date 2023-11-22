<?php

declare(strict_types=1);

namespace Psl\Comparison;

/**
 * @template T
 *
 * @param T $a
 * @param T $b
 */
function greater(mixed $a, mixed $b): bool
{
    return compare($a, $b) === Order::Greater;
}
