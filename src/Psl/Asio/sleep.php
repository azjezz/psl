<?php

declare(strict_types=1);

namespace Psl\Asio;

use Amp;

/**
 * Sleeps for the specified number of milliseconds.
 *
 * @return Awaitable<void>
 */
function sleep(int $milliseconds): Awaitable
{
    $delay = new Amp\Delayed($milliseconds, (static function (): void {
        // ever wondered how to create "void"?
    })());

    return new Internal\AwaitablePromise($delay);
}
