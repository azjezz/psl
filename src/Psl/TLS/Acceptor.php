<?php

declare(strict_types=1);

namespace Psl\TLS;

use Psl\Network;
use Psl\TLS\Exception\HandshakeFailedException;

use function is_resource;
use function stream_context_set_options;

/**
 * Performs TLS server handshakes on incoming streams.
 *
 * Takes an accepted plain stream and upgrades it to a TLS-encrypted stream
 * using the provided {@see ServerConfig}.
 *
 * Usage:
 *   $acceptor = new Acceptor(ServerConfig::create($certificate));
 *   while (true) {
 *       $stream = $listener->accept();
 *       $tls = $acceptor->accept($stream);
 *   }
 */
final readonly class Acceptor
{
    public function __construct(
        private ServerConfig $config,
    ) {}

    /**
     * Perform a TLS handshake on an incoming stream.
     *
     * @throws HandshakeFailedException If the TLS handshake fails.
     * @throws Network\Exception\RuntimeException If the stream is not available.
     */
    public function accept(Network\StreamInterface $stream): StreamInterface
    {
        $resource = $stream->getStream();
        if (!is_resource($resource)) {
            throw new Network\Exception\RuntimeException('Stream resource is not available.');
        }

        $ssl_context = Internal\server_ssl_context($this->config);
        stream_context_set_options($resource, ['ssl' => $ssl_context]);

        $crypto_method = Internal\crypto_method(
            $this->config->minimumVersion,
            $this->config->maximumVersion,
            server: true,
        );

        Internal\enable_crypto($resource, $crypto_method);

        $state = Internal\extract_connection_state($resource);

        return new Internal\Stream($stream, $state);
    }
}
