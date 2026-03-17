<?php

declare(strict_types=1);

namespace Psl\TLS\Internal;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\TLS\Exception\HandshakeFailedException;
use Revolt\EventLoop;
use Revolt\EventLoop\Suspension;

use function stream_socket_enable_crypto;

/**
 * Perform an asynchronous TLS handshake on the given stream resource.
 *
 * This function enables crypto on a stream using Revolt's event loop for non-blocking I/O.
 * When {@see stream_socket_enable_crypto()} returns 0 (handshake in progress), it suspends
 * and retries when the stream becomes readable.
 *
 * @param resource $stream The stream resource to enable TLS on.
 * @param int $cryptoMethod The crypto method bitmask (STREAM_CRYPTO_METHOD_*).
 *
 * @throws HandshakeFailedException If the TLS handshake fails.
 * @throws CancelledException If the cancellation token is cancelled during the handshake.
 *
 * @internal
 *
 * @codeCoverageIgnore
 */
function enable_crypto(
    mixed $stream,
    int $cryptoMethod,
    CancellationTokenInterface $cancellation = new NullCancellationToken(),
): void {
    if ($cancellation->cancellable) {
        $cancellation->throwIfCancelled();
    }

    // Try the initial handshake; stream is already non-blocking from ResourceHandle
    $result = @stream_socket_enable_crypto($stream, true, $cryptoMethod);

    if (true === $result) {
        return;
    }

    if (false === $result) {
        throw new HandshakeFailedException('TLS handshake failed.');
    }

    // $result === 0, handshake in progress; wait for the event loop
    /** @var Suspension<null> */
    $suspension = EventLoop::getSuspension();

    $watcher = '';
    $watcher = EventLoop::onReadable($stream, static function () use (
        &$watcher,
        $suspension,
        $stream,
        $cryptoMethod,
    ): void {
        $result = @stream_socket_enable_crypto($stream, true, $cryptoMethod);

        if (true === $result) {
            EventLoop::cancel($watcher);
            $suspension->resume(null);

            return;
        }

        if (false === $result) {
            EventLoop::cancel($watcher);

            $suspension->throw(new HandshakeFailedException('TLS handshake failed.'));

            return;
        }
        // $result === 0 means handshake is still in progress, wait for more data
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
}
