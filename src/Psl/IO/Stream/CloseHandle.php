<?php

declare(strict_types=1);

namespace Psl\IO\Stream;

use Psl\IO;
use Psl\IO\Internal;

/**
 * @codeCoverageIgnore
 */
final class CloseHandle implements IO\CloseHandleInterface
{
    private IO\CloseHandleInterface $handle;

    /**
     * @param resource|object $stream
     *
     * @throws IO\Exception\BlockingException If unable to set the stream to non-blocking mode.
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
}
