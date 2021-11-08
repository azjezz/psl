<?php

declare(strict_types=1);

namespace Psl\Network;

use Psl\IO;

/**
 * A handle representing a connection between processes.
 *
 * It is possible for both ends to be connected to the same process,
 * and to either be local or across a network.
 */
interface SocketInterface extends IO\CloseReadWriteHandleInterface
{
    /**
     * Returns the address of the local side of the socket.
     *
     * @throws IO\Exception\AlreadyClosedException If the socket has already been closed.
     * @throws Exception\RuntimeException If unable to retrieve local address.
     */
    public function getLocalAddress(): Address;

    /**
     * Returns the address of the remote side of the socket.
     *
     * @throws IO\Exception\AlreadyClosedException If the socket has already been closed.
     * @throws Exception\RuntimeException If unable to retrieve peer address.
     */
    public function getPeerAddress(): Address;
}
