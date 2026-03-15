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
 * Represents a parsed TLS ClientHello, allowing SNI/ALPN inspection before completing the handshake.
 *
 * Obtained from {@see LazyAcceptor::accept()}, this class lets you inspect the client's
 * SNI hostname and ALPN protocols before choosing a {@see ServerConfiguration} for the handshake.
 */
final readonly class ClientHello
{
    /**
     * @param ?non-empty-string $serverName
     * @param ?list<non-empty-string> $alpnProtocols
     */
    public function __construct(
        private Network\StreamInterface $stream,
        private null|string $serverName,
        private null|array $alpnProtocols,
    ) {}

    /**
     * The SNI hostname the client requested, or null if not present.
     *
     * @return ?non-empty-string
     */
    public function getServerName(): null|string
    {
        return $this->serverName;
    }

    /**
     * The ALPN protocols the client advertised, or null if not present.
     *
     * @return ?list<non-empty-string>
     */
    public function getAlpnProtocols(): null|array
    {
        return $this->alpnProtocols;
    }

    /**
     * Complete the TLS handshake with the given configuration.
     *
     * @throws HandshakeFailedException If the TLS handshake fails.
     * @throws Network\Exception\RuntimeException If the stream is not available.
     * @throws CancelledException If the cancellation token is cancelled during the handshake.
     */
    public function complete(
        ServerConfiguration $serverConfiguration,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): StreamInterface {
        $resource = $this->stream->getStream();
        if (!is_resource($resource)) {
            throw new Network\Exception\RuntimeException('Stream resource is not available.');
        }

        $sslContext = Internal\server_ssl_context($serverConfiguration);
        stream_context_set_options($resource, ['ssl' => $sslContext]);

        $cryptoMethod = Internal\crypto_method(
            $serverConfiguration->minimumVersion,
            $serverConfiguration->maximumVersion,
            server: true,
        );

        Internal\enable_crypto($resource, $cryptoMethod, $cancellation);

        $state = Internal\extract_connection_state($resource);

        return new Internal\Stream($this->stream, $state);
    }
}
