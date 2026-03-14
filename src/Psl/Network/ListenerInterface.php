<?php

declare(strict_types=1);

namespace Psl\Network;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;

/**
 * Interface for accepting incoming connections.
 *
 * Replaces the previous ServerInterface and StreamServerInterface with a unified listener abstraction.
 */
interface ListenerInterface extends SocketInterface
{
    /**
     * Accept the next pending connection.
     *
     * Will block until a new connection is available.
     *
     * @throws Exception\RuntimeException If failed to accept incoming connection.
     * @throws Exception\AlreadyStoppedException If the listener has already been closed.
     * @throws CancelledException If the cancellation token is cancelled while waiting.
     */
    public function accept(CancellationTokenInterface $cancellation = new NullCancellationToken()): StreamInterface;

    /**
     * Stop listening; open connections are not closed.
     */
    public function close(): void;
}
