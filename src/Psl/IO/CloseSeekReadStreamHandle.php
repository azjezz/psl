<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\DateTime\Duration;
use Psl\IO;

/**
 * @codeCoverageIgnore
 */
final class CloseSeekReadStreamHandle implements CloseSeekReadStreamHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;

    private CloseSeekReadStreamHandleInterface $handle;

    /**
     * @param resource $stream
     */
    public function __construct(mixed $stream)
    {
        $this->handle = new Internal\ResourceHandle($stream, read: true, write: false, seek: true, close: true);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function reachedEndOfDataSource(): bool
    {
        return $this->handle->reachedEndOfDataSource();
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function tryRead(null|int $max_bytes = null): string
    {
        return $this->handle->tryRead($max_bytes);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function read(null|int $max_bytes = null, null|Duration $timeout = null): string
    {
        return $this->handle->read($max_bytes, $timeout);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function seek(int $offset): void
    {
        $this->handle->seek($offset);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function tell(): int
    {
        return $this->handle->tell();
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function close(): void
    {
        $this->handle->close();
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getStream(): mixed
    {
        return $this->handle->getStream();
    }
}
