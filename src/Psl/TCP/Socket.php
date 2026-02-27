<?php

declare(strict_types=1);

namespace Psl\TCP;

use Psl\DateTime\Duration;
use Psl\Network;
use Revolt\EventLoop;
use Socket as PHPSocket;

use function socket_bind;
use function socket_connect;
use function socket_create;
use function socket_export_stream;
use function socket_get_option;
use function socket_getsockname;
use function socket_last_error;
use function socket_listen;
use function socket_set_option;
use function socket_strerror;

use const AF_INET;
use const AF_INET6;
use const SO_KEEPALIVE;
use const SO_RCVBUF;
use const SO_REUSEADDR;
use const SO_REUSEPORT;
use const SO_SNDBUF;
use const SOCK_STREAM;
use const SOL_SOCKET;
use const SOL_TCP;
use const TCP_NODELAY;

/**
 * A TCP socket that can be configured before connecting or listening.
 *
 * Mirrors Tokio's TcpSocket pattern: create → configure → connect/listen.
 *
 * Requires the `ext-sockets` extension.
 */
final class Socket
{
    private PHPSocket $socket;
    private bool $consumed = false;

    private function __construct(PHPSocket $socket)
    {
        $this->socket = $socket;
    }

    /**
     * Create a new IPv4 TCP socket.
     *
     * @throws Network\Exception\RuntimeException If the sockets extension is not available or socket creation fails.
     */
    public static function createV4(): self
    {
        return self::doCreate(AF_INET);
    }

    /**
     * Create a new IPv6 TCP socket.
     *
     * @throws Network\Exception\RuntimeException If the sockets extension is not available or socket creation fails.
     */
    public static function createV6(): self
    {
        return self::doCreate(AF_INET6);
    }

    /**
     * Bind the socket to a local address.
     *
     * @param non-empty-string $host
     * @param int<0, 65535> $port
     *
     * @throws Network\Exception\RuntimeException If bind fails.
     */
    public function bind(string $host, int $port = 0): void
    {
        $this->ensureNotConsumed();

        if (!@socket_bind($this->socket, $host, $port)) {
            throw new Network\Exception\RuntimeException(
                'Failed to bind socket: ' . socket_strerror(socket_last_error($this->socket)),
            );
        }
    }

    /**
     * Connect the socket to a remote address and return a TCP stream.
     *
     * This consumes the socket — it cannot be reused after calling connect().
     *
     * @param non-empty-string $host
     * @param int<0, 65535> $port
     *
     * @throws Network\Exception\RuntimeException If connect fails.
     * @throws Network\Exception\TimeoutException If the operation times out.
     */
    public function connect(string $host, int $port, null|Duration $timeout = null): StreamInterface
    {
        $this->ensureNotConsumed();
        $this->consumed = true;

        if (!@socket_connect($this->socket, $host, $port)) {
            $errno = socket_last_error($this->socket);
            // EINPROGRESS — non-blocking connect, need to wait
            if ($errno === 115 || $errno === 36 || $errno === 10_035) {
                $stream = $this->exportStream();
                $this->waitForConnect($stream, $timeout);

                return new Internal\Stream($stream);
            }

            throw new Network\Exception\RuntimeException('Failed to connect socket: ' . socket_strerror($errno));
        }

        return new Internal\Stream($this->exportStream());
    }

    /**
     * Start listening for incoming connections and return a TCP listener.
     *
     * This consumes the socket — it cannot be reused after calling listen().
     *
     * @param int<1, max> $backlog Maximum length of the queue of pending connections.
     * @param int<1, max> $idle_connections Maximum number of idle connections to buffer.
     *
     * @throws Network\Exception\RuntimeException If listen fails.
     */
    public function listen(int $backlog = 128, int $idle_connections = 256): ListenerInterface
    {
        $this->ensureNotConsumed();
        $this->consumed = true;

        if (!@socket_listen($this->socket, $backlog)) {
            throw new Network\Exception\RuntimeException(
                'Failed to listen on socket: ' . socket_strerror(socket_last_error($this->socket)),
            );
        }

        return new Internal\Listener($this->exportStream(), $idle_connections);
    }

    /**
     * Get the local address the socket is bound to.
     *
     * @throws Network\Exception\RuntimeException If unable to retrieve local address.
     */
    public function getLocalAddress(): Network\Address
    {
        $this->ensureNotConsumed();

        $address = '';
        $port = 0;
        if (!@socket_getsockname($this->socket, $address, $port)) {
            throw new Network\Exception\RuntimeException(
                'Failed to get socket name: ' . socket_strerror(socket_last_error($this->socket)),
            );
        }

        return Network\Address::tcp($address !== '' ? $address : '0.0.0.0', $port);
    }

    public function setReuseAddress(bool $enabled): void
    {
        $this->setOption(SOL_SOCKET, SO_REUSEADDR, $enabled);
    }

    public function getReuseAddress(): bool
    {
        return (bool) $this->getOption(SOL_SOCKET, SO_REUSEADDR);
    }

    public function setReusePort(bool $enabled): void
    {
        $this->setOption(SOL_SOCKET, SO_REUSEPORT, $enabled);
    }

    public function getReusePort(): bool
    {
        return (bool) $this->getOption(SOL_SOCKET, SO_REUSEPORT);
    }

    public function setNoDelay(bool $enabled): void
    {
        $this->setOption(SOL_TCP, TCP_NODELAY, $enabled);
    }

    public function getNoDelay(): bool
    {
        return (bool) $this->getOption(SOL_TCP, TCP_NODELAY);
    }

    public function setSendBufferSize(int $size): void
    {
        $this->setOption(SOL_SOCKET, SO_SNDBUF, $size);
    }

    public function getSendBufferSize(): int
    {
        return $this->getOption(SOL_SOCKET, SO_SNDBUF);
    }

    public function setReceiveBufferSize(int $size): void
    {
        $this->setOption(SOL_SOCKET, SO_RCVBUF, $size);
    }

    public function getReceiveBufferSize(): int
    {
        return $this->getOption(SOL_SOCKET, SO_RCVBUF);
    }

    public function setKeepAlive(bool $enabled): void
    {
        $this->setOption(SOL_SOCKET, SO_KEEPALIVE, $enabled);
    }

    public function getKeepAlive(): bool
    {
        return (bool) $this->getOption(SOL_SOCKET, SO_KEEPALIVE);
    }

    private static function doCreate(int $domain): self
    {
        $socket = @socket_create($domain, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            throw new Network\Exception\RuntimeException(
                'Failed to create TCP socket. Ensure ext-sockets is installed.',
            );
        }

        return new self($socket);
    }

    private function setOption(int $level, int $option, bool|int $value): void
    {
        $this->ensureNotConsumed();

        if (!@socket_set_option($this->socket, $level, $option, $value ? 1 : 0)) {
            throw new Network\Exception\RuntimeException(
                'Failed to set socket option: ' . socket_strerror(socket_last_error($this->socket)),
            );
        }
    }

    private function getOption(int $level, int $option): int
    {
        $this->ensureNotConsumed();

        $value = @socket_get_option($this->socket, $level, $option);
        if ($value === false) {
            throw new Network\Exception\RuntimeException(
                'Failed to get socket option: ' . socket_strerror(socket_last_error($this->socket)),
            );
        }

        return (int) $value;
    }

    private function ensureNotConsumed(): void
    {
        if ($this->consumed) {
            throw new Network\Exception\RuntimeException('Socket has already been consumed by connect() or listen().');
        }
    }

    /**
     * Export the underlying socket as a stream resource or object.
     *
     * @return PHPSocket
     */
    private function exportStream(): PHPSocket
    {
        $stream = @socket_export_stream($this->socket);
        if ($stream === false) {
            throw new Network\Exception\RuntimeException('Failed to export socket as stream.');
        }

        /** @var PHPSocket */
        return $stream;
    }

    /**
     * Wait for a non-blocking connect to complete.
     */
    private function waitForConnect(PHPSocket $stream, null|Duration $timeout): void
    {
        $suspension = EventLoop::getSuspension();
        $timeout_watcher = null;
        // @mago-expect analysis:invalid-argument
        $write_watcher = EventLoop::onWritable($stream, static function (string $watcher) use ($suspension): void {
            EventLoop::cancel($watcher);
            $suspension->resume(false);
        });

        if ($timeout !== null) {
            $timeout_watcher = EventLoop::delay($timeout->getTotalSeconds(), static function () use (
                $suspension,
                $write_watcher,
            ): void {
                EventLoop::cancel($write_watcher);
                $suspension->resume(true);
            });
        }

        /** @var bool $timed_out */
        $timed_out = $suspension->suspend();
        if ($timeout_watcher !== null) {
            EventLoop::cancel($timeout_watcher);
        }

        EventLoop::cancel($write_watcher);

        if ($timed_out) {
            throw new Network\Exception\TimeoutException('Connection timed out.');
        }
    }
}
