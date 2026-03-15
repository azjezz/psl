<?php

declare(strict_types=1);

namespace Psl\TLS;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\Default\DefaultInterface;
use Psl\Network;
use Psl\TLS\Exception\HandshakeFailedException;

use function is_resource;
use function stream_context_set_options;

/**
 * Performs TLS client handshakes on existing streams.
 *
 * Takes a plain stream and upgrades it to a TLS-encrypted stream
 * using the provided {@see ClientConfiguration}.
 *
 * Usage:
 *   $connector = Connector::default();
 *   $tls = $connector->connect(TCP\connect('example.com', 443), 'example.com');
 */
final readonly class Connector implements DefaultInterface
{
    public function __construct(
        private ClientConfiguration $clientConfiguration = new ClientConfiguration(),
    ) {}

    /**
     * @pure
     */
    #[Override]
    public static function default(): static
    {
        return new self();
    }

    /**
     * Perform a TLS handshake on the given stream.
     *
     * @param non-empty-string|null $serverName SNI hostname override. If null, uses the config's peerName.
     *
     * @throws HandshakeFailedException If the TLS handshake fails.
     * @throws Network\Exception\RuntimeException If the stream is not available.
     * @throws CancelledException If the cancellation token is cancelled during the handshake.
     */
    public function connect(
        Network\StreamInterface $stream,
        null|string $serverName = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): StreamInterface {
        $resource = $stream->getStream();
        if (!is_resource($resource)) {
            throw new Network\Exception\RuntimeException('Stream resource is not available.');
        }

        $clientConfiguration = $this->clientConfiguration;
        if ($serverName !== null && $clientConfiguration->peerName === null) {
            $clientConfiguration = $clientConfiguration->withPeerName($serverName);
        }

        $sslContext = Internal\client_ssl_context($clientConfiguration);
        stream_context_set_options($resource, ['ssl' => $sslContext]);

        $cryptoMethod = Internal\crypto_method(
            $clientConfiguration->minimumVersion,
            $clientConfiguration->maximumVersion,
            server: false,
        );

        Internal\enable_crypto($resource, $cryptoMethod, $cancellation);

        $state = Internal\extract_connection_state($resource);

        return new Internal\Stream($stream, $state);
    }
}
