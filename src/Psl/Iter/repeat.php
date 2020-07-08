<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl;
use Psl\Gen;

/**
 * Repeat an element a given number of times. By default the element is repeated
 * indefinitely.
 *
 * Examples:
 *
 *     Iter\repeat(42, 5)
 *     => iter(42, 42, 42, 42, 42)
 *
 *     Iter\repeat(1, 6)
 *     => iter(1, 1, 1, 1, 1, 1)
 *
 * @psalm-template T
 *
 * @psalm-param    T   $value Value to repeat
 * @psalm-param    int $num   Number of repetitions
 *
 * @psalm-return   Iterator<int, T>
 *
 * @see            Gen\repeat()
 *
 * @throws Psl\Exception\InvariantViolationException If $num is negative.
 */
function repeat($value, int $num): Iterator
{
    return new Iterator(Gen\repeat($value, $num));
}
