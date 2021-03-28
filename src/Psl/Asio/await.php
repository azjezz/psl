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
 * @return T
 */
function await(Awaitable $awaitable)
{
    /** @psalm-suppress MissingThrowsDocblock - not our problem. */
    return Amp\await(new Internal\PromiseAwaitable($awaitable));
}
