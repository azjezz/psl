<?php

declare(strict_types=1);

namespace Psl\IO;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO;

/**
 * @codeCoverageIgnore
 */
final class CloseWriteStreamHandle implements StreamHandleInterface, WriteHandleInterface, CloseHandleInterface
{
    use IO\WriteHandleConvenienceMethodsTrait;

    private StreamHandleInterface&WriteHandleInterface&CloseHandleInterface $handle;

    /**
     * @param resource $stream
     */
    public function __construct(mixed $stream)
    {
        $this->handle = new Internal\ResourceHandle($stream, read: false, write: true, seek: false, close: true);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function tryWrite(string $bytes): int
    {
        return $this->handle->tryWrite($bytes);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function write(string $bytes, CancellationTokenInterface $cancellation = new NullCancellationToken()): int
    {
        return $this->handle->write($bytes, $cancellation);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function isClosed(): bool
    {
        return $this->handle->isClosed();
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function close(): void
    {
        $this->handle->close();
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getStream(): mixed
    {
        return $this->handle->getStream();
    }
}
