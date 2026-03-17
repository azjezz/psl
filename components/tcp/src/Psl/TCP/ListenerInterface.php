<?php

declare(strict_types=1);

namespace Psl\TCP;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\Network;

/**
 * A TCP listener that accepts incoming TCP connections.
 */
interface ListenerInterface extends Network\ListenerInterface
{
    /**
     * Accept the next pending TCP connection.
     *
     * @throws Network\Exception\RuntimeException If failed to accept incoming connection.
     * @throws Network\Exception\AlreadyStoppedException If the listener has already been closed.
     * @throws CancelledException If the cancellation token is cancelled while waiting.
     */
    public function accept(CancellationTokenInterface $cancellation = new NullCancellationToken()): StreamInterface;
}
