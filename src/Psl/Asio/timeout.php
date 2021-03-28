<?php

declare(strict_types=1);

namespace Psl\Asio;

use Amp;

/**
 * Creates an artificial timeout for any wait handle.
 *
 * If the timeout expires before the awaitable is finished, the returned awaitable fails with an instance of
 * `Psl\Asio\Exception\TimeoutException`.
 *
 * @template T
 *
 * @param Awaitable<T> $awaitable Wait handle to which the timeout is applied.
 * @param int $timeout Timeout in milliseconds.
 *
 * @return Awaitable<T>
 */
function timeout(Awaitable $awaitable, int $timeout): Awaitable
{
    /** @psalm-suppress MissingThrowsDocblock - it's a promise. */
    return new Internal\AwaitablePromise(
        Amp\Promise\timeout(new Internal\PromiseAwaitable($awaitable), $timeout)
    );
}
