<?php

declare(strict_types=1);

namespace Psl\TCP;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\Network;

/**
 * Interface for establishing TCP connections.
 *
 * Implementations can provide different connection strategies such as
 * direct connection, retry with backoff, proxy tunneling, or static routing.
 */
interface ConnectorInterface
{
    /**
     * Connect to a TCP host and return a stream.
     *
     * @param non-empty-string $host
     * @param int<0, 65535> $port
     *
     * @throws Network\Exception\RuntimeException If the connection fails.
     * @throws CancelledException If the operation was cancelled.
     */
    public function connect(
        string $host,
        int $port,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): StreamInterface;
}
