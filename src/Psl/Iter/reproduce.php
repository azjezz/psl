<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl;
use Psl\Math;
use Psl\Vec;

/**
 * Creates an iterator containing all numbers between the start and end value
 * (inclusive) with a certain step.
 *
 * Examples:
 *
 *     Iter\reproduce(Fun\Identity(), 5)
 *     => Iter(1, 2, 3, 4, 5)
 *
 * @template T
 *
 * @param (callable(int): T) $factory
 * @param int|null $number How many times should the factory be executed?
 *
 * @throws Psl\Exception\InvariantViolationException If $number < 1
 *
 * @return Iterator<int, T>
 *
 * @deprecated since 1.2, use Vec\reproduce instead.
 * @see Vec\reproduce()
 */
function reproduce(callable $factory, ?int $number = null): Iterator
{
    /** @var int $max */
    $max = $number ?? Math\INFINITY;
    Psl\invariant($max >= 1, 'The number of times you want to reproduce must be at least 1.');

    return Iterator::from(static function () use ($max, $factory): Generator {
        for ($current = 1; $current <= $max; $current++) {
            yield $factory($current);
        }
    });
}
