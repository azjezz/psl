<?php

declare(strict_types=1);

namespace Psl\Network;

use Psl\IO;

/**
 * Generic interface for a class able to accept socket connections.
 */
interface ServerInterface extends IO\CloseHandleInterface
{
    /**
     * Retrieve the next pending connection.
     *
     * Will wait for new connections if none are pending.
     *
     * @throws Exception\RuntimeException In case failed to accept incoming connection.
     * @throws Exception\AlreadyStoppedException In case the server socket has already been closed.
     */
    public function nextConnection(): SocketInterface;

    /**
     * Return the local (listening) address for the server.
     *
     * @throws Exception\RuntimeException In case failed to retrieve local address.
     * @throws Exception\AlreadyStoppedException In case the server socket has already been closed.
     */
    public function getLocalAddress(): Address;

    /**
     * Stop listening; open connection are not closed.
     */
    public function close(): void;
}
