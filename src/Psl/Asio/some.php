<?php

declare(strict_types=1);

namespace Psl\Asio;

use Amp;
use Psl;
use Psl\Iter;
use Psl\Vec;

/**
 * The returned wait handle will only fail if the given number of required awaitables fail.
 *
 * @template T
 *
 * @param iterable<array-key, Awaitable<T>> $awaitables A list of wait handles.
 * @param int $required Number of wait handles that must succeed for the returned awaitable to succeed.
 *
 * @throws Psl\Exception\InvariantViolationException
 *
 * @return Awaitable<list<T>>
 */
function some(iterable $awaitables, int $required = 1): Awaitable
{
    Psl\invariant($required >= 0, '$required must be a non-negative.');

    $pending = Iter\count($awaitables);

    Psl\invariant($required <= $pending, '$required is too large.');

    if (Iter\is_empty($awaitables)) {
        return new Internal\FinishedAwaitable([]);
    }

    if (empty($awaitables)) {
        return new Internal\FinishedAwaitable([]);
    }

    $promises = Vec\map(
        $awaitables,
        static fn (Awaitable $awaitable): Amp\Promise => new Internal\PromiseAwaitable($awaitable)
    );

    return new Internal\AwaitablePromise(Amp\Promise\all($promises));
}
