<?php

declare(strict_types=1);

namespace Psl\IO;

use Override;

/**
 * @codeCoverageIgnore
 */
final class CloseStreamHandle implements CloseHandleInterface, StreamHandleInterface
{
    private CloseHandleInterface&StreamHandleInterface $handle;

    /**
     * @param resource $stream
     */
    public function __construct(mixed $stream)
    {
        $this->handle = new Internal\ResourceHandle($stream, read: false, write: false, seek: false, close: true);
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
