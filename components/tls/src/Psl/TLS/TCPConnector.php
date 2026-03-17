<?php

declare(strict_types=1);

namespace Psl\TLS;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\Network;
use Psl\TCP;

/**
 * A TCP connector that upgrades connections to TLS after establishing them.
 *
 * Composes a {@see TCP\ConnectorInterface} for the underlying TCP connection
 * and a {@see Connector} for the TLS handshake, allowing each layer to be
 * configured independently.
 *
 * The resulting streams can be used with {@see TCP\SocketPoolInterface} to
 * enable connection pooling for TLS connections (e.g. DNS-over-TLS).
 */
final readonly class TCPConnector implements TCP\ConnectorInterface
{
    public function __construct(
        private TCP\ConnectorInterface $tcpConnector,
        private Connector $tlsConnector,
    ) {}

    /**
     * Connect to the given host over TCP and perform a TLS handshake.
     *
     * The $host parameter is used both as the TCP connection target and as the
     * TLS Server Name Indication (SNI) value, unless overridden by the
     * {@see ClientConfiguration::$peerName} of the TLS connector.
     *
     * @param non-empty-string $host
     * @param int<0, 65535> $port
     *
     * @throws Network\Exception\RuntimeException If the TCP connection fails.
     * @throws CancelledException If the operation was cancelled.
     * @throws Exception\HandshakeFailedException If the TLS handshake fails.
     */
    public function connect(
        string $host,
        int $port,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): TCP\StreamInterface {
        $stream = $this->tcpConnector->connect($host, $port, $cancellation);

        return $this->tlsConnector->connect($stream, $host, $cancellation);
    }
}
