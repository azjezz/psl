<?php

declare(strict_types=1);

namespace Psl\Asio;

use Amp;
use Error;
use Psl\Exception;

/**
 * Wait for the given wait handle to finish.
 *
 * @template T
 *
 * @param Awaitable<T> $awaitable
 *
 * @throws Exception\RuntimeException If an internal error occurred.
 *
 * @return T
 */
function await(Awaitable $awaitable)
{
    return Amp\await(new Internal\PromiseAwaitable($awaitable));
}
