<?php

declare(strict_types=1);

namespace Psl\TCP;

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
     */
    public function accept(): StreamInterface;
}
