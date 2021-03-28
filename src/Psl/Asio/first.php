<?php

declare(strict_types=1);

namespace Psl\Asio;

use Amp;
use Psl;
use Psl\Iter;
use Psl\Vec;

/**
 * Returns an awaitable that finishes when the first wait handle finishes,
 * and fails only if all wait handles fail.
 *
 * @template T
 *
 * @param iterable<Awaitable<T>> $awaitables
 *
 * @throws Psl\Exception\InvariantViolationException If $awaitables is empty.
 *
 * @return Awaitable<T>
 */
function first(iterable $awaitables): Awaitable
{
    Psl\invariant(!Iter\is_empty($awaitables), '$awaitables is empty.');

    $promises = Vec\map(
        $awaitables,
        static fn (Awaitable $awaitable): Amp\Promise => new Internal\PromiseAwaitable($awaitable)
    );

    return new Internal\AwaitablePromise(Amp\Promise\first($promises));
}
