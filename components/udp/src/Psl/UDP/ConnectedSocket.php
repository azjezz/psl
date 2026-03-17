<?php

declare(strict_types=1);

namespace Psl\UDP;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\IO;
use Psl\Network;

use function fclose;
use function is_resource;
use function stream_set_blocking;
use function stream_socket_get_name;
use function stream_socket_recvfrom;
use function stream_socket_sendto;

use const STREAM_PEEK;

/**
 * A connected UDP socket for communicating with a single peer.
 *
 * Obtained via {@see Socket::connect()} or {@see connect()}.
 */
final class ConnectedSocket implements Network\SocketInterface, IO\StreamHandleInterface
{
    /**
     * @var resource|closed-resource|null
     */
    private mixed $stream;

    private readonly Network\Address $localAddress;

    /**
     * @internal Use {@see Socket::connect()} or {@see connect()} to obtain a ConnectedSocket.
     *
     * @param resource $stream
     */
    public function __construct(
        mixed $stream,
        private readonly Network\Address $peerAddress,
    ) {
        $this->stream = $stream;
        stream_set_blocking($stream, false);

        $name = @stream_socket_get_name($stream, false);
        if ($name === false) {
            // @codeCoverageIgnoreStart
            throw new Network\Exception\RuntimeException('Failed to get local address.');
            // @codeCoverageIgnoreEnd
        }

        $this->localAddress = Internal\parse_address($name);
    }

    /**
     * Send a datagram to the connected peer.
     *
     * @return int<0, max> Number of bytes sent.
     *
     * @throws Network\Exception\RuntimeException If the send fails.
     * @throws Network\Exception\InvalidArgumentException If the datagram exceeds the maximum size.
     * @throws CancelledException If the operation is cancelled.
     * @throws IO\Exception\AlreadyClosedException If the socket has already been closed.
     */
    public function send(string $data, CancellationTokenInterface $cancellation = new NullCancellationToken()): int
    {
        Internal\validate_payload_size($data);
        $stream = $this->getResource();

        Internal\wait_writable($stream, $cancellation);

        $result = @stream_socket_sendto($stream, $data);
        if ($result === false || $result === -1) {
            // @codeCoverageIgnoreStart
            throw new Network\Exception\RuntimeException('Failed to send UDP datagram.');
            // @codeCoverageIgnoreEnd
        }

        /** @var int<0, max> */
        return $result;
    }

    /**
     * Receive a datagram from the connected peer.
     *
     * @param positive-int $maxBytes
     *
     * @throws Network\Exception\RuntimeException If the receive fails.
     * @throws CancelledException If the operation is cancelled.
     * @throws IO\Exception\AlreadyClosedException If the socket has already been closed.
     */
    public function receive(
        int $maxBytes,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        $stream = $this->getResource();

        Internal\await_readable($stream, $cancellation);

        $data = @stream_socket_recvfrom($stream, $maxBytes, 0);
        if ($data === false) {
            // @codeCoverageIgnoreStart
            throw new Network\Exception\RuntimeException('Failed to receive UDP datagram.');
            // @codeCoverageIgnoreEnd
        }

        return $data;
    }

    /**
     * Peek at an incoming datagram without consuming it.
     *
     * @param positive-int $maxBytes
     *
     * @throws Network\Exception\RuntimeException If the peek fails.
     * @throws CancelledException If the operation is cancelled.
     * @throws IO\Exception\AlreadyClosedException If the socket has already been closed.
     */
    public function peek(int $maxBytes, CancellationTokenInterface $cancellation = new NullCancellationToken()): string
    {
        $stream = $this->getResource();

        Internal\await_readable($stream, $cancellation);

        $data = @stream_socket_recvfrom($stream, $maxBytes, STREAM_PEEK);
        if ($data === false) {
            // @codeCoverageIgnoreStart
            throw new Network\Exception\RuntimeException('Failed to peek UDP datagram.');
            // @codeCoverageIgnoreEnd
        }

        return $data;
    }

    #[Override]
    public function getLocalAddress(): Network\Address
    {
        return $this->localAddress;
    }

    /**
     * Get the peer address this socket is connected to.
     */
    public function getPeerAddress(): Network\Address
    {
        return $this->peerAddress;
    }

    /**
     * @return resource|object|null
     */
    #[Override]
    public function getStream(): mixed
    {
        if (!is_resource($this->stream)) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return $this->stream;
    }

    // @codeCoverageIgnoreStart
    #[Override]
    public function isClosed(): bool
    {
        return !is_resource($this->stream);
    }

    // @codeCoverageIgnoreEnd

    #[Override]
    public function close(): void
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }

        $this->stream = null;
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * @return resource
     *
     * @throws IO\Exception\AlreadyClosedException If the socket has already been closed.
     */
    private function getResource(): mixed
    {
        if (!is_resource($this->stream)) {
            throw new IO\Exception\AlreadyClosedException('UDP socket has already been closed.');
        }

        return $this->stream;
    }
}
