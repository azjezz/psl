<?php

declare(strict_types=1);

namespace Psl\IO\Stream;

use Psl\IO\Internal;

/**
 * @codeCoverageIgnore
 */
final class CloseHandle implements CloseHandleInterface
{
    private CloseHandleInterface $handle;

    /**
     * @param resource|object $stream
     */
    public function __construct(mixed $stream)
    {
        $this->handle = new Internal\ResourceHandle($stream, read: false, write: false, seek: false);
    }

    /**
     * {@inheritDoc}
     */
    public function close(): void
    {
        $this->handle->close();
    }

    /**
     * @return object|resource|null
     */
    public function getStream(): mixed
    {
        return $this->handle->getStream();
    }
}
