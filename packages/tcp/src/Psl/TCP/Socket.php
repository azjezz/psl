<?php

declare(strict_types=1);

namespace Psl\TCP;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\Network;

use const PHP_OS_FAMILY;

/**
 * A TCP socket that can be configured before connecting or listening.
 *
 * Create a socket, bind to an address, then consume it by calling connect() or listen().
 */
final class Socket
{
    private bool $consumed = false;

    /**
     * @var null|array{non-empty-string, int<0, 65535>}
     */
    private null|array $bindAddress = null;

    private function __construct(
        private readonly bool $ipv6,
    ) {}

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
     * This consumes the socket; it cannot be reused after calling connect().
     *
     * @param non-empty-string $host
     * @param int<0, 65535> $port
     *
     * @throws Network\Exception\RuntimeException If connect fails.
     * @throws CancelledException If the operation was cancelled.
     */
    public function connect(
        string $host,
        int $port,
        ConnectConfiguration $configuration = new ConnectConfiguration(),
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): StreamInterface {
        $this->ensureNotConsumed();
        $this->consumed = true;

        $context = $this->buildContext($configuration);

        $stream = Network\Internal\socket_connect("tcp://{$host}:{$port}", $context, $cancellation);

        return new Internal\Stream($stream);
    }

    /**
     * Start listening for incoming connections and return a TCP listener.
     *
     * This consumes the socket; it cannot be reused after calling listen().
     *
     * @throws Network\Exception\RuntimeException If listen fails.
     */
    public function listen(ListenConfiguration $configuration = new ListenConfiguration()): ListenerInterface
    {
        $this->ensureNotConsumed();
        $this->consumed = true;

        if ($this->bindAddress === null) {
            throw new Network\Exception\RuntimeException(
                'Cannot listen without binding to an address first. Call bind() before listen().',
            );
        }

        [$host, $port] = $this->bindAddress;

        $context = $this->buildContext($configuration);
        $context['socket']['backlog'] = $configuration->backlog;

        $stream = Network\Internal\server_listen("tcp://{$host}:{$port}", $context);

        return new Internal\Listener($stream, $configuration->idleConnections);
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

    /**
     * @return array{socket: array<string, mixed>}
     */
    private function buildContext(ListenConfiguration|ConnectConfiguration $configuration): array
    {
        if ($configuration instanceof ConnectConfiguration) {
            return ['socket' => [
                'tcp_nodelay' => $configuration->noDelay,
            ]];
        }

        $socket = [
            'tcp_nodelay' => $configuration->noDelay,
            'so_reuseaddr' => PHP_OS_FAMILY === 'Windows' ? $configuration->reusePort : $configuration->reuseAddress,
            'so_reuseport' => $configuration->reusePort,
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
