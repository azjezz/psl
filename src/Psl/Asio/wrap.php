<?php

declare(strict_types=1);

namespace Psl\Asio;

use Psl\Result;

/**
 * @template T
 *
 * @param Awaitable<T> $awaitable
 *
 * @return Awaitable<Result\ResultInterface<T>>
 */
function wrap(Awaitable $awaitable): Awaitable
{
    return async(static fn (): Result\ResultInterface => Result\wrap(
        static fn () => await($awaitable)
    ));
}
