<?php

declare(strict_types=1);

namespace Psl\TLS\Internal;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO;
use Psl\Network;
use Psl\TLS;

use function is_resource;
use function stream_socket_enable_crypto;

/**
 * TLS-wrapped stream implementation.
 *
 * Wraps an existing stream that has had TLS enabled on it. Delegates all I/O
 * to the inner stream and adds TLS connection state + proper TLS shutdown on close.
 *
 * @internal
 *
 * @codeCoverageIgnore
 */
final class Stream implements TLS\StreamInterface
{
    use IO\WriteHandleConvenienceMethodsTrait;
    use IO\ReadHandleConvenienceMethodsTrait;

    public function __construct(
        private readonly Network\StreamInterface $inner,
        private readonly TLS\ConnectionState $state,
    ) {}

    #[Override]
    public function getState(): TLS\ConnectionState
    {
        return $this->state;
    }

    #[Override]
    public function reachedEndOfDataSource(): bool
    {
        return $this->inner->reachedEndOfDataSource();
    }

    /**
     * @param ?positive-int $max_bytes
     */
    #[Override]
    public function tryRead(null|int $max_bytes = null): string
    {
        return $this->inner->tryRead($max_bytes);
    }

    /**
     * @param ?positive-int $max_bytes
     */
    #[Override]
    public function read(
        null|int $max_bytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        return $this->inner->read($max_bytes, $cancellation);
    }

    /**
     * @return int<0, max>
     */
    #[Override]
    public function tryWrite(string $bytes): int
    {
        return $this->inner->tryWrite($bytes);
    }

    /**
     * @return int<0, max>
     */
    #[Override]
    public function write(string $bytes, CancellationTokenInterface $cancellation = new NullCancellationToken()): int
    {
        return $this->inner->write($bytes, $cancellation);
    }

    /**
     * @return resource|object|null
     */
    #[Override]
    public function getStream(): mixed
    {
        return $this->inner->getStream();
    }

    #[Override]
    public function getLocalAddress(): Network\Address
    {
        return $this->inner->getLocalAddress();
    }

    #[Override]
    public function getPeerAddress(): Network\Address
    {
        return $this->inner->getPeerAddress();
    }

    /**
     * @param positive-int $max_bytes
     */
    #[Override]
    public function peek(int $max_bytes, CancellationTokenInterface $cancellation = new NullCancellationToken()): string
    {
        return $this->inner->peek($max_bytes, $cancellation);
    }

    #[Override]
    public function shutdown(): void
    {
        $this->inner->shutdown();
    }

    #[Override]
    public function close(): void
    {
        // Send TLS close_notify before closing the underlying stream.
        $stream = $this->inner->getStream();
        if (is_resource($stream)) {
            @stream_socket_enable_crypto($stream, false);
        }

        $this->inner->close();
    }

    public function __destruct()
    {
        $this->close();
    }
}
