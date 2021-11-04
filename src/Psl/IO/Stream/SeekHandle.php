<?php

declare(strict_types=1);

namespace Psl\IO\Stream;

use Psl\IO;
use Psl\IO\Internal;

/**
 * @codeCoverageIgnore
 */
final class SeekHandle implements IO\SeekHandleInterface
{
    private IO\SeekHandleInterface $handle;

    /**
     * @param resource|object $stream
     *
     * @throws IO\Exception\BlockingException If unable to set the stream to non-blocking mode.
     */
    public function __construct(mixed $stream)
    {
        $this->handle = new Internal\ResourceHandle($stream, read: false, write: false, seek: true);
    }

    /**
     * {@inheritDoc}
     */
    public function seek(int $offset): void
    {
        $this->handle->seek($offset);
    }

    /**
     * {@inheritDoc}
     */
    public function tell(): int
    {
        return $this->handle->tell();
    }
}
