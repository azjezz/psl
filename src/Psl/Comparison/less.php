<?php

declare(strict_types=1);

namespace Psl\Comparison;

/**
 * @template T
 *
 * @param T $a
 * @param T $b
 */
function less(mixed $a, mixed $b): bool
{
    return compare($a, $b) === Order::Less;
}
