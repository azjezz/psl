<?php

declare(strict_types=1);

namespace Psl\Asio;

/**
 * Sleeps for the specified number of milliseconds.
 *
 * @return Awaitable<void>
 */
function sleep(int $milliseconds): Awaitable
{
    return new Internal\Delayed($milliseconds);
}
