<?php

declare(strict_types=1);

namespace Psl\TLS;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\Network;

/**
 * A TLS listener that accepts incoming connections and performs TLS handshakes.
 */
interface ListenerInterface extends Network\ListenerInterface
{
    /**
     * Accept the next pending connection and perform a TLS handshake.
     *
     * @throws Network\Exception\RuntimeException If failed to accept incoming connection.
     * @throws Network\Exception\AlreadyStoppedException If the listener has already been closed.
     * @throws Exception\HandshakeFailedException If the TLS handshake fails.
     * @throws CancelledException If the cancellation token is cancelled while waiting.
     */
    public function accept(CancellationTokenInterface $cancellation = new NullCancellationToken()): StreamInterface;
}
