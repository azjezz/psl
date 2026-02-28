<?php

declare(strict_types=1);

namespace Psl\TLS;

use Override;
use Psl\Default\DefaultInterface;
use Psl\Network;
use Psl\TLS\Exception\HandshakeFailedException;

use function is_resource;
use function stream_context_set_options;

/**
 * Performs TLS client handshakes on existing streams.
 *
 * Takes a plain stream and upgrades it to a TLS-encrypted stream
 * using the provided {@see ClientConfig}.
 *
 * Usage:
 *   $connector = Connector::default();
 *   $tls = $connector->connect(TCP\connect('example.com', 443), 'example.com');
 */
final readonly class Connector implements DefaultInterface
{
    public function __construct(
        private ClientConfig $config = new ClientConfig(),
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
     * @param non-empty-string|null $server_name SNI hostname override. If null, uses the config's peerName.
     *
     * @throws HandshakeFailedException If the TLS handshake fails.
     * @throws Network\Exception\RuntimeException If the stream is not available.
     */
    public function connect(Network\StreamInterface $stream, null|string $server_name = null): StreamInterface
    {
        $resource = $stream->getStream();
        if (!is_resource($resource)) {
            throw new Network\Exception\RuntimeException('Stream resource is not available.');
        }

        $config = $this->config;
        if ($server_name !== null && $config->peerName === null) {
            $config = $config->withPeerName($server_name);
        }

        $ssl_context = Internal\client_ssl_context($config);
        stream_context_set_options($resource, ['ssl' => $ssl_context]);

        $crypto_method = Internal\crypto_method($config->minimumVersion, $config->maximumVersion, server: false);

        Internal\enable_crypto($resource, $crypto_method);

        $state = Internal\extract_connection_state($resource);

        return new Internal\Stream($stream, $state);
    }
}
