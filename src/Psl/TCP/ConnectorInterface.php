<?php

declare(strict_types=1);

namespace Psl\TCP;

use Psl\DateTime\Duration;
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
     * @throws Network\Exception\TimeoutException If the operation times out.
     */
    public function connect(string $host, int $port, null|Duration $timeout = null): StreamInterface;
}
