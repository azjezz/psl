<?php

declare(strict_types=1);

namespace Psl\UDP;

use Override;
use Psl\DateTime\Duration;
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
    }

    /**
     * Send a datagram to the connected peer.
     *
     * @return int<0, max> Number of bytes sent.
     *
     * @throws Network\Exception\RuntimeException If the send fails.
     * @throws Network\Exception\InvalidArgumentException If the datagram exceeds the maximum size.
     * @throws IO\Exception\TimeoutException If the operation times out.
     * @throws IO\Exception\AlreadyClosedException If the socket has already been closed.
     */
    public function send(string $data, null|Duration $timeout = null): int
    {
        Internal\validate_payload_size($data);
        $stream = $this->getResource();

        if ($timeout !== null) {
            Internal\wait_writable($stream, $timeout);
        }

        $result = @stream_socket_sendto($stream, $data);
        if ($result === false || $result === -1) {
            throw new Network\Exception\RuntimeException('Failed to send UDP datagram.');
        }

        /** @var int<0, max> */
        return $result;
    }

    /**
     * Receive a datagram from the connected peer.
     *
     * @param positive-int $max_bytes
     *
     * @throws Network\Exception\RuntimeException If the receive fails.
     * @throws IO\Exception\TimeoutException If the operation times out.
     * @throws IO\Exception\AlreadyClosedException If the socket has already been closed.
     */
    public function receive(int $max_bytes, null|Duration $timeout = null): string
    {
        $stream = $this->getResource();

        Internal\await_readable($stream, $timeout);

        $data = @stream_socket_recvfrom($stream, $max_bytes, 0);
        if ($data === false) {
            throw new Network\Exception\RuntimeException('Failed to receive UDP datagram.');
        }

        return $data;
    }

    /**
     * Peek at an incoming datagram without consuming it.
     *
     * @param positive-int $max_bytes
     *
     * @throws Network\Exception\RuntimeException If the peek fails.
     * @throws IO\Exception\TimeoutException If the operation times out.
     * @throws IO\Exception\AlreadyClosedException If the socket has already been closed.
     */
    public function peek(int $max_bytes, null|Duration $timeout = null): string
    {
        $stream = $this->getResource();

        Internal\await_readable($stream, $timeout);

        $data = @stream_socket_recvfrom($stream, $max_bytes, STREAM_PEEK);
        if ($data === false) {
            throw new Network\Exception\RuntimeException('Failed to peek UDP datagram.');
        }

        return $data;
    }

    /**
     * Get the local address this socket is bound to.
     *
     * @throws Network\Exception\RuntimeException If unable to retrieve local address.
     * @throws IO\Exception\AlreadyClosedException If the socket has already been closed.
     */
    #[Override]
    public function getLocalAddress(): Network\Address
    {
        $stream = $this->getResource();
        $name = @stream_socket_get_name($stream, false);
        if ($name === false) {
            throw new Network\Exception\RuntimeException('Failed to get local address.');
        }

        return Internal\parse_address($name);
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
            return null;
        }

        return $this->stream;
    }

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
