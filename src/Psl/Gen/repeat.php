<?php

declare(strict_types=1);

namespace Psl\Gen;

use Generator;
use Psl;
use Psl\Math;

/**
 * Repeat an element a given number of times. By default the element is repeated
 * indefinitely.
 *
 * Examples:
 *
 *     Gen\repeat(42, 5)
 *     => Gen(42, 42, 42, 42, 42)
 *
 *     Gen\repeat(1)
 *     => Gen(1, 1, 1, 1, 1, 1, 1, 1, 1, ...)
 *
 * @psalm-template T
 *
 * @psalm-param T   $value Value to repeat
 * @psalm-param int $num   Number of repetitions (defaults to INF)
 *
 * @psalm-return Generator<int, T, mixed, void>
 *
 * @psalm-pure
 */
function repeat($value, ?int $num = null): Generator
{
    Psl\invariant(null === $num || $num >= 0, 'Number of repetitions must be non-negative');

    /** @var int $num */
    $num ??= Math\INFINITY;
    for ($i = 0; $i < $num; ++$i) {
        yield $value;
    }
}
