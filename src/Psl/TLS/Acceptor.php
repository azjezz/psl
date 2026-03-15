<?php

declare(strict_types=1);

namespace Psl\TLS;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\Network;
use Psl\TLS\Exception\HandshakeFailedException;

use function is_resource;
use function stream_context_set_options;

/**
 * Performs TLS server handshakes on incoming streams.
 *
 * Takes an accepted plain stream and upgrades it to a TLS-encrypted stream
 * using the provided {@see ServerConfiguration}.
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
        private ServerConfiguration $serverConfiguration,
    ) {}

    /**
     * Perform a TLS handshake on an incoming stream.
     *
     * @throws HandshakeFailedException If the TLS handshake fails.
     * @throws Network\Exception\RuntimeException If the stream is not available.
     * @throws CancelledException If the cancellation token is cancelled during the handshake.
     */
    public function accept(
        Network\StreamInterface $stream,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): StreamInterface {
        $resource = $stream->getStream();
        if (!is_resource($resource)) {
            throw new Network\Exception\RuntimeException('Stream resource is not available.');
        }

        $sslContext = Internal\server_ssl_context($this->serverConfiguration);
        stream_context_set_options($resource, ['ssl' => $sslContext]);

        $cryptoMethod = Internal\crypto_method(
            $this->serverConfiguration->minimumVersion,
            $this->serverConfiguration->maximumVersion,
            server: true,
        );

        Internal\enable_crypto($resource, $cryptoMethod, $cancellation);

        $state = Internal\extract_connection_state($resource);

        return new Internal\Stream($stream, $state);
    }
}
