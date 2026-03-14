<?php

declare(strict_types=1);

namespace Psl\Network;

use Psl\IO;

/**
 * Base interface for all network sockets.
 *
 * Provides access to the local address and close functionality.
 */
interface SocketInterface extends IO\CloseHandleInterface
{
    /**
     * Returns the address of the local side of the socket.
     */
    public function getLocalAddress(): Address;
}
