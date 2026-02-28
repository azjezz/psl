<?php

declare(strict_types=1);

namespace Psl\TCP;

use Psl\DateTime\Duration;
use Psl\Network;
use Psl\OS;

/**
 * A TCP socket that can be configured before connecting or listening.
 *
 * Create a socket, configure options (reuse address, no delay, etc.),
 * then consume it by calling connect() or listen().
 */
final class Socket
{
    private bool $ipv6;
    private bool $consumed = false;

    private bool $reuseAddress = false;
    private bool $reusePort = false;
    private bool $noDelay = false;

    /**
     * @var null|array{non-empty-string, int<0, 65535>}
     */
    private null|array $bindAddress = null;

    private function __construct(bool $ipv6)
    {
        $this->ipv6 = $ipv6;
    }

    /**
     * Create a new IPv4 TCP socket.
     */
    public static function createV4(): self
    {
        return new self(false);
    }

    /**
     * Create a new IPv6 TCP socket.
     */
    public static function createV6(): self
    {
        return new self(true);
    }

    /**
     * Bind the socket to a local address.
     *
     * @param non-empty-string $host
     * @param int<0, 65535> $port
     */
    public function bind(string $host, int $port = 0): void
    {
        $this->ensureNotConsumed();

        $this->bindAddress = [$host, $port];
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

        $context = $this->buildContext();

        $stream = Network\Internal\socket_connect("tcp://{$host}:{$port}", $context, $timeout);

        return new Internal\Stream($stream);
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

        if ($this->bindAddress === null) {
            throw new Network\Exception\RuntimeException(
                'Cannot listen without binding to an address first. Call bind() before listen().',
            );
        }

        [$host, $port] = $this->bindAddress;

        $context = $this->buildContext();
        $context['socket']['backlog'] = $backlog;

        $stream = Network\Internal\server_listen("tcp://{$host}:{$port}", $context);

        return new Internal\Listener($stream, $idle_connections);
    }

    /**
     * Get the local address the socket is bound to.
     *
     * @throws Network\Exception\RuntimeException If no address has been bound.
     */
    public function getLocalAddress(): Network\Address
    {
        $this->ensureNotConsumed();

        if ($this->bindAddress === null) {
            throw new Network\Exception\RuntimeException('Socket has not been bound to an address. Call bind() first.');
        }

        [$host, $port] = $this->bindAddress;

        return Network\Address::tcp($host, $port);
    }

    public function setReuseAddress(bool $enabled): void
    {
        $this->ensureNotConsumed();

        $this->reuseAddress = $enabled;
    }

    public function getReuseAddress(): bool
    {
        $this->ensureNotConsumed();

        return $this->reuseAddress;
    }

    public function setReusePort(bool $enabled): void
    {
        $this->ensureNotConsumed();

        $this->reusePort = $enabled;
    }

    public function getReusePort(): bool
    {
        $this->ensureNotConsumed();

        return $this->reusePort;
    }

    public function setNoDelay(bool $enabled): void
    {
        $this->ensureNotConsumed();

        $this->noDelay = $enabled;
    }

    public function getNoDelay(): bool
    {
        $this->ensureNotConsumed();

        return $this->noDelay;
    }

    /**
     * @return array{socket: array{tcp_nodelay: bool, so_reuseaddr: bool, so_reuseport: bool, ipv6_v6only?: bool, bindto?: string}}
     */
    private function buildContext(): array
    {
        $socket = [
            'tcp_nodelay' => $this->noDelay,
            'so_reuseaddr' => OS\is_windows() ? $this->reusePort : $this->reuseAddress,
            'so_reuseport' => $this->reusePort,
        ];

        if ($this->ipv6) {
            $socket['ipv6_v6only'] = true;
        }

        if ($this->bindAddress !== null) {
            [$host, $port] = $this->bindAddress;
            $socket['bindto'] = "{$host}:{$port}";
        }

        return ['socket' => $socket];
    }

    private function ensureNotConsumed(): void
    {
        if ($this->consumed) {
            throw new Network\Exception\RuntimeException('Socket has already been consumed by connect() or listen().');
        }
    }
}
