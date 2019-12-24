<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl;
use Psl\Math;

/**
 * Repeat an element a given number of times. By default the element is repeated
 * indefinitely.
 *
 * Examples:
 *
 *     Iter\repeat(42, 5)
 *     => iter(42, 42, 42, 42, 42)
 *
 *     Iter\repeat(1)
 *     => iter(1, 1, 1, 1, 1, 1, 1, 1, 1, ...)
 *
 * @psalm-template T
 *
 * @psalm-param T   $value Value to repeat
 * @psalm-param int $num   Number of repetitions (defaults to INF)
 *
 * @psalm-return iterable<T>
 *
 * @psalm-pure
 */
function repeat($value, ?int $num = null): iterable
{
    Psl\invariant(null === $num || $num >= 0, 'Number of repetitions must be non-negative');

    $num = $num ?? Math\INFINITY;
    for ($i = 0; $i < $num; ++$i) {
        yield $value;
    }
}
