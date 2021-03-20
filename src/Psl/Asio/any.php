<?php

declare(strict_types=1);

namespace Psl\Asio;

use Psl;

/**
 * The returned Awaitable will only fail if more that 1 wait handle fails execution.
 *
 * @template T
 *
 * @param iterable<array-key, Awaitable<T>> $awaitables
 *
 * @throws Psl\Exception\InvariantViolationException
 *
 * @return Awaitable<list<T>>
 */
function any(iterable $awaitables): Awaitable
{
    return some($awaitables, 1);
}
