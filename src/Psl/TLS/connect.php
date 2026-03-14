<?php

declare(strict_types=1);

namespace Psl\TLS;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\Network;
use Psl\TCP;

/**
 * Connect to a host over TCP and perform a TLS handshake in one step.
 *
 * This is a convenience function that combines {@see TCP\connect()} and
 * {@see Connector::connect()} into a single call.
 *
 * @param non-empty-string $host Hostname or IP to connect to.
 * @param int<0, 65535> $port Port to connect to.
 * @param ClientConfig|null $config TLS configuration. Defaults to {@see ClientConfig::default()}.
 * @param CancellationTokenInterface $cancellation Cancellation token.
 *
 * @throws Network\Exception\RuntimeException If the TCP connection fails.
 * @throws CancelledException If the operation was cancelled.
 * @throws Exception\HandshakeFailedException If the TLS handshake fails.
 */
function connect(
    string $host,
    int $port,
    null|ClientConfig $config = null,
    CancellationTokenInterface $cancellation = new NullCancellationToken(),
): StreamInterface {
    $stream = TCP\connect($host, $port, cancellation: $cancellation);

    $connector = new Connector($config ?? ClientConfig::default());

    return $connector->connect($stream, $host, cancellation: $cancellation);
}
