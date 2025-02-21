<?php

declare(strict_types=1);

namespace Psl\IO;

/**
 * @codeCoverageIgnore
 */
final class CloseSeekStreamHandle implements CloseSeekStreamHandleInterface
{
    private CloseSeekStreamHandleInterface $handle;

    /**
     * @param resource $stream
     */
    public function __construct(mixed $stream)
    {
        $this->handle = new Internal\ResourceHandle($stream, read: false, write: false, seek: true, close: true);
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
