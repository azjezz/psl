<?php

declare(strict_types=1);

namespace Psl\IO\Stream;

use Psl\IO;
use Psl\IO\Internal;

/**
 * @codeCoverageIgnore
 */
final class CloseReadHandle implements IO\CloseReadHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;

    private IO\CloseReadHandleInterface $handle;

    /**
     * @param resource|object $stream
     *
     * @throws IO\Exception\BlockingException If unable to set the stream to non-blocking mode.
     */
    public function __construct(mixed $stream)
    {
        $this->handle = new Internal\ResourceHandle($stream, read: true, write: false, seek: false);
    }

    /**
     * {@inheritDoc}
     */
    public function readImmediately(?int $max_bytes = null): string
    {
        return $this->handle->readImmediately($max_bytes);
    }

    /**
     * {@inheritDoc}
     */
    public function read(?int $max_bytes = null, ?int $timeout_ms = null): string
    {
        return $this->handle->read($max_bytes, $timeout_ms);
    }

    /**
     * {@inheritDoc}
     */
    public function close(): void
    {
        $this->handle->close();
    }
}
