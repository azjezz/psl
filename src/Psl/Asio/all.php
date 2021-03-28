<?php

declare(strict_types=1);

namespace Psl\Asio;

use Amp;
use Psl\Vec;

/**
 * Returns a wait handle that succeeds when all awaitables succeed, and fails if any awaitable fails.
 *
 * Returned awaitable succeeds with a list of values used to succeed each contained awaitable.
 *
 * @template T
 *
 * @param iterable<Awaitable<T>> $awaitables
 *
 * @return Awaitable<list<T>>
 */
function all(iterable $awaitables): Awaitable
{
    if (empty($awaitables)) {
        return new Internal\FinishedAwaitable([]);
    }

    $promises = Vec\map(
        $awaitables,
        static fn (Awaitable $awaitable): Amp\Promise => new Internal\PromiseAwaitable($awaitable)
    );

    return new Internal\AwaitablePromise(Amp\Promise\all($promises));
}
