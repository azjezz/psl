<?php

declare(strict_types=1);

namespace Psl\Unix;

use Psl\DateTime\Duration;
use Psl\Network;
use Revolt\EventLoop;
use Socket as PHPSocket;

use function socket_bind;
use function socket_connect;
use function socket_create;
use function socket_export_stream;
use function socket_getsockname;
use function socket_last_error;
use function socket_listen;
use function socket_set_nonblock;
use function socket_strerror;

use const AF_UNIX;
use const SOCK_STREAM;

/**
 * A Unix domain socket that can be configured before connecting or listening.
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
     * Create a new Unix domain socket.
     *
     * @throws Network\Exception\RuntimeException If the platform is Windows, sockets extension is unavailable, or socket creation fails.
     */
    public static function create(): self
    {
        Internal\assert_not_windows();

        $socket = @socket_create(AF_UNIX, SOCK_STREAM, 0);
        if ($socket === false) {
            throw new Network\Exception\RuntimeException(
                'Failed to create Unix socket. Ensure ext-sockets is installed.',
            );
        }

        return new self($socket);
    }

    /**
     * Bind the socket to a filesystem path.
     *
     * @param non-empty-string $path
     *
     * @throws Network\Exception\RuntimeException If bind fails.
     */
    public function bind(string $path): void
    {
        $this->ensureNotConsumed();

        if (!@socket_bind($this->socket, $path)) {
            throw new Network\Exception\RuntimeException(
                'Failed to bind socket: ' . socket_strerror(socket_last_error($this->socket)),
            );
        }
    }

    /**
     * Connect the socket to a remote path and return a Unix stream.
     *
     * This consumes the socket — it cannot be reused after calling connect().
     *
     * @param non-empty-string $path
     *
     * @throws Network\Exception\RuntimeException If connect fails.
     * @throws Network\Exception\TimeoutException If the connection times out.
     */
    public function connect(string $path, null|Duration $timeout = null): StreamInterface
    {
        $this->ensureNotConsumed();
        $this->consumed = true;

        socket_set_nonblock($this->socket);

        if (!@socket_connect($this->socket, $path)) {
            $errno = socket_last_error($this->socket);
            // EINPROGRESS: 115 (Linux), 36 (macOS)
            if ($errno === 115 || $errno === 36) {
                $stream = $this->exportStream();
                $this->waitForConnect($stream, $timeout);

                return new Internal\Stream($stream);
            }

            throw new Network\Exception\RuntimeException('Failed to connect socket: ' . socket_strerror($errno));
        }

        return new Internal\Stream($this->exportStream());
    }

    /**
     * Start listening for incoming connections and return a Unix listener.
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
        if (!@socket_getsockname($this->socket, $address)) {
            throw new Network\Exception\RuntimeException(
                'Failed to get socket name: ' . socket_strerror(socket_last_error($this->socket)),
            );
        }

        return Network\Address::unix($address !== '' ? $address : '/');
    }

    /**
     * Wait for a non-blocking connect to complete.
     *
     * @param resource|PHPSocket $stream
     */
    private function waitForConnect(mixed $stream, null|Duration $timeout): void
    {
        $suspension = EventLoop::getSuspension();
        $timeout_watcher = null;

        // @mago-expect analysis:possibly-invalid-argument
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

    private function ensureNotConsumed(): void
    {
        if ($this->consumed) {
            throw new Network\Exception\RuntimeException('Socket has already been consumed by connect() or listen().');
        }
    }

    /**
     * Export the underlying socket as a stream resource.
     *
     * @return resource|PHPSocket
     */
    private function exportStream(): mixed
    {
        $stream = @socket_export_stream($this->socket);
        if ($stream === false) {
            throw new Network\Exception\RuntimeException('Failed to export socket as stream.');
        }

        return $stream;
    }
}
