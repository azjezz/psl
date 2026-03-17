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
final class CloseSeekReadWriteStreamHandle implements
    CloseHandleInterface,
    SeekHandleInterface,
    ReadHandleInterface,
    WriteHandleInterface,
    StreamHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;
    use IO\WriteHandleConvenienceMethodsTrait;

    private CloseHandleInterface&SeekHandleInterface&ReadHandleInterface&WriteHandleInterface&StreamHandleInterface $handle;

    /**
     * @param resource $stream
     */
    public function __construct(mixed $stream)
    {
        $this->handle = new Internal\ResourceHandle($stream, read: true, write: true, seek: true, close: true);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function reachedEndOfDataSource(): bool
    {
        return $this->handle->reachedEndOfDataSource();
    }

    /**
     * @param ?positive-int $maxBytes the maximum number of bytes to read
     *
     * @inheritDoc
     */
    #[Override]
    public function tryRead(null|int $maxBytes = null): string
    {
        return $this->handle->tryRead($maxBytes);
    }

    /**
     * @param ?positive-int $maxBytes the maximum number of bytes to read
     *
     * @inheritDoc
     */
    #[Override]
    public function read(
        null|int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        return $this->handle->read($maxBytes, $cancellation);
    }

    /**
     * @return int<0, max>
     *
     * @inheritDoc
     */
    #[Override]
    public function tryWrite(string $bytes): int
    {
        return $this->handle->tryWrite($bytes);
    }

    /**
     * @return int<0, max>
     *
     * @inheritDoc
     */
    #[Override]
    public function write(string $bytes, CancellationTokenInterface $cancellation = new NullCancellationToken()): int
    {
        return $this->handle->write($bytes, $cancellation);
    }

    /**
     * @param int<0, max> $offset
     *
     * @inheritDoc
     */
    #[Override]
    public function seek(int $offset): void
    {
        $this->handle->seek($offset);
    }

    /**
     * @return int<0, max>
     *
     * @inheritDoc
     */
    #[Override]
    public function tell(): int
    {
        return $this->handle->tell();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function isClosed(): bool
    {
        return $this->handle->isClosed();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function close(): void
    {
        $this->handle->close();
    }

    /**
     * @return resource|object|null
     *
     * @inheritDoc
     */
    #[Override]
    public function getStream(): mixed
    {
        return $this->handle->getStream();
    }
}
