<?php

declare(strict_types=1);

namespace Psl\TCP;

use Psl\DateTime\Duration;
use Psl\Network;

/**
 * A pool of reusable TCP connections.
 *
 * Connections are keyed by host:port. When a connection is checked out,
 * an idle connection is reused if available; otherwise a new one is created.
 * Checked-in connections are kept alive for reuse until an idle timeout expires.
 */
interface SocketPoolInterface
{
    /**
     * Get a connection from the pool, creating one if needed.
     *
     * @param non-empty-string $host
     * @param int<0, 65535> $port
     *
     * @throws Network\Exception\RuntimeException If the connection fails.
     * @throws Network\Exception\TimeoutException If the operation times out.
     */
    public function checkout(string $host, int $port, null|Duration $timeout = null): StreamInterface;

    /**
     * Return a connection to the pool for reuse.
     *
     * The connection will be kept alive until the idle timeout expires.
     */
    public function checkin(StreamInterface $stream): void;

    /**
     * Remove a connection from the pool permanently and close it.
     */
    public function clear(StreamInterface $stream): void;

    /**
     * Close all idle pooled connections.
     */
    public function close(): void;
}
