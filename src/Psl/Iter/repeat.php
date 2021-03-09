<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl;
use Psl\Math;
use Psl\Vec;

/**
 * Repeat an element a given number of times. By default the element is repeated
 * indefinitely.
 *
 * Examples:
 *
 *     Iter\repeat(42, 5)
 *     => Iter(42, 42, 42, 42, 42)
 *
 *     Iter\repeat(1)
 *     => Iter(1, 1, 1, 1, 1, 1, 1, 1, 1, ...)
 *
 * @template T
 *
 * @param T $value Value to repeat
 * @param int $num Number of repetitions (defaults to INF)
 *
 * @throws Psl\Exception\InvariantViolationException If $num is negative.
 *
 * @return Iterator<int, T>
 *
 * @deprecated since 1.2, use Vec\fill instead.
 * @see Vec\fill()
 */
function repeat($value, ?int $num = null): Iterator
{
    Psl\invariant(null === $num || $num >= 0, 'Number of repetitions must be non-negative.');

    return Iterator::from(static function () use ($value, $num): Generator {
        if (null === $num) {
            /** @var int $num */
            $num = Math\INFINITY;
        }

        for ($i = 0; $i < $num; ++$i) {
            yield $value;
        }
    });
}
