<?php

declare(strict_types=1);

namespace Psl\Asio;

use Amp;

/**
 * Execute the given callable asynchronously.
 *
 * @template T
 *
 * @param (callable(): T) $callable
 *
 * @return Awaitable<T>
 */
function async(callable $callable): Awaitable
{
    return new Internal\AwaitablePromise(Amp\async($callable));
}
