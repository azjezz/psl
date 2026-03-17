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
use Revolt\EventLoop;

use function is_resource;
use function stream_socket_recvfrom;

use const STREAM_PEEK;

/**
 * Lazily accepts TLS connections by peeking at the ClientHello before completing the handshake.
 *
 * Peeks at the raw TLS ClientHello to inspect SNI hostname and ALPN protocols,
 * then lets you choose the appropriate {@see ServerConfiguration} before completing the handshake.
 *
 * Usage:
 *   $lazy = LazyAcceptor::default();
 *   while (true) {
 *       $stream = $listener->accept();
 *       $hello = $lazy->accept($stream);
 *       $config = match($hello->getServerName()) {
 *           'api.example.com' => $apiConfig,
 *           default => $defaultConfig,
 *       };
 *       $tls = $hello->complete($config);
 *   }
 */
final class LazyAcceptor implements DefaultInterface
{
    /**
     * @pure
     */
    #[Override]
    public static function default(): static
    {
        return new self();
    }

    /**
     * Peek at the ClientHello without consuming data, then return a {@see ClientHello}
     * that can be used to complete the handshake with a chosen config.
     *
     * @throws Network\Exception\RuntimeException If the stream is not available.
     * @throws HandshakeFailedException If the ClientHello data cannot be read or parsed.
     * @throws CancelledException If the cancellation token is cancelled while waiting.
     */
    public function accept(
        Network\StreamInterface $stream,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): ClientHello {
        $resource = $stream->getStream();
        if (!is_resource($resource)) {
            throw new Network\Exception\RuntimeException('Stream resource is not available.');
        }

        if ($cancellation->cancellable) {
            $cancellation->throwIfCancelled();
        }

        // Wait for data to be available
        $suspension = EventLoop::getSuspension();
        $watcher = EventLoop::onReadable($resource, static function (string $watcher) use ($suspension): void {
            EventLoop::cancel($watcher);
            $suspension->resume(null);
        });

        $cancellationId = null;
        if ($cancellation->cancellable) {
            $cancellationId = $cancellation->subscribe(static function (CancelledException $e) use (
                &$watcher,
                $suspension,
            ): void {
                EventLoop::cancel($watcher);
                $suspension->throw($e);
            });
        }

        try {
            $suspension->suspend();
        } finally {
            EventLoop::cancel($watcher);
            if (null !== $cancellationId) {
                $cancellation->unsubscribe($cancellationId);
            }
        }

        // Resource may have been closed by another fiber during suspend.
        // @mago-expect analysis:redundant-type-comparison,impossible-condition
        if (!is_resource($resource)) {
            throw new Network\Exception\RuntimeException('Stream closed while waiting for ClientHello.');
        }

        // Peek at enough data to parse the ClientHello.
        // TLS records can be up to 16384 bytes, but ClientHello is typically < 1024.
        // We peek up to 16384 to handle large ClientHellos with many extensions.
        $data = @stream_socket_recvfrom($resource, 16_384, STREAM_PEEK);
        if ($data === false || $data === '') {
            throw new HandshakeFailedException('Failed to peek ClientHello data.');
        }

        $parsed = Internal\ClientHelloParser::parse($data);
        if ($parsed === null) {
            throw new HandshakeFailedException('Failed to parse TLS ClientHello.');
        }

        return new ClientHello($stream, $parsed['server_name'], $parsed['alpn_protocols']);
    }
}
