<?php

declare(strict_types=1);

namespace Psl\Network;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\IO;

/**
 * A bidirectional network stream with peek and shutdown support.
 *
 * This is the core type for connected network streams (TCP, Unix, TLS).
 * It extends SocketInterface with read/write capabilities, peer address access,
 * data peeking (reading without consuming), and write-side shutdown.
 */
interface StreamInterface extends
    SocketInterface,
    IO\ReadHandleInterface,
    IO\WriteHandleInterface,
    IO\StreamHandleInterface
{
    /**
     * Returns the address of the remote side of the connection.
     */
    public function getPeerAddress(): Address;

    /**
     * Read up to $maxBytes without consuming them from the receive buffer.
     *
     * The data remains in the buffer and will be returned by subsequent read() calls.
     *
     * @param positive-int $maxBytes
     *
     * @throws IO\Exception\AlreadyClosedException If the stream has already been closed.
     * @throws IO\Exception\RuntimeException If an error occurred during the peek operation.
     * @throws CancelledException If the operation was cancelled.
     */
    public function peek(int $maxBytes, CancellationTokenInterface $cancellation = new NullCancellationToken()): string;

    /**
     * Shut down the write side of the connection.
     *
     * The remote peer will see EOF on their read side. Reads on this side still work.
     *
     * @throws IO\Exception\AlreadyClosedException If the stream has already been closed.
     * @throws IO\Exception\RuntimeException If unable to shut down the write side.
     */
    public function shutdown(): void;
}
