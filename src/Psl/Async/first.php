<?php

declare(strict_types=1);

namespace Psl\Async;

use Psl;

/**
 * Unwraps the first completed awaitable.
 *
 * If you want the first awaitable completed without an error, use {@see any()} instead.
 *
 * @template T
 *
 * @param iterable<Awaitable<T>> $awaitables
 *
 * @throws Psl\Exception\InvariantViolationException If $awaitables is empty.
 *
 * @return T
 *
 * @codeCoverageIgnore
 */
function first(iterable $awaitables): mixed
{
    foreach (Awaitable::iterate($awaitables) as $first) {
        return $first->await();
    }

    Psl\invariant_violation('No awaitables were provided.');
}
