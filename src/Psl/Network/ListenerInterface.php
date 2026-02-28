<?php

declare(strict_types=1);

namespace Psl\Network;

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
     */
    public function accept(): StreamInterface;

    /**
     * Stop listening; open connections are not closed.
     */
    public function close(): void;
}
