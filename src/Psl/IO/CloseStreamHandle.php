<?php

declare(strict_types=1);

namespace Psl\IO;

/**
 * @codeCoverageIgnore
 */
final class CloseStreamHandle implements CloseStreamHandleInterface
{
    private CloseStreamHandleInterface $handle;

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
    public function close(): void
    {
        $this->handle->close();
    }

    /**
     * {@inheritDoc}
     */
    public function getStream(): mixed
    {
        return $this->handle->getStream();
    }
}
