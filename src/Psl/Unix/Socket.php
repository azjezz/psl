<?php

declare(strict_types=1);

namespace Psl\Unix;

use Psl\DateTime\Duration;
use Psl\Network;

/**
 * A Unix domain socket that can be configured before connecting or listening.
 */
final class Socket
{
    private bool $consumed = false;

    /**
     * @var null|non-empty-string
     */
    private null|string $bindPath = null;

    private function __construct() {}

    /**
     * Create a new Unix domain socket.
     *
     * @throws Network\Exception\RuntimeException If the platform is Windows.
     */
    public static function create(): self
    {
        Internal\assert_not_windows();

        return new self();
    }

    /**
     * Bind the socket to a filesystem path.
     *
     * @param non-empty-string $path
     */
    public function bind(string $path): void
    {
        $this->ensureNotConsumed();

        $this->bindPath = $path;
    }

    /**
     * Connect the socket to a remote path and return a Unix stream.
     *
     * This consumes the socket; it cannot be reused after calling connect().
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

        $stream = Network\Internal\socket_connect("unix://{$path}", timeout: $timeout);

        return new Internal\Stream($stream);
    }

    /**
     * Start listening for incoming connections and return a Unix listener.
     *
     * This consumes the socket; it cannot be reused after calling listen().
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

        if ($this->bindPath === null) {
            throw new Network\Exception\RuntimeException(
                'Cannot listen without binding to a path first. Call bind() before listen().',
            );
        }

        $context = ['socket' => [
            'backlog' => $backlog,
        ]];

        $stream = Network\Internal\server_listen("unix://{$this->bindPath}", $context);

        return new Internal\Listener($stream, $idle_connections);
    }

    /**
     * Get the local address the socket is bound to.
     *
     * @throws Network\Exception\RuntimeException If no path has been bound.
     */
    public function getLocalAddress(): Network\Address
    {
        $this->ensureNotConsumed();

        if ($this->bindPath === null) {
            throw new Network\Exception\RuntimeException('Socket has not been bound to a path. Call bind() first.');
        }

        return Network\Address::unix($this->bindPath);
    }

    private function ensureNotConsumed(): void
    {
        if ($this->consumed) {
            throw new Network\Exception\RuntimeException('Socket has already been consumed by connect() or listen().');
        }
    }
}
