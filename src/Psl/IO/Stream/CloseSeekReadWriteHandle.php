<?php

declare(strict_types=1);

namespace Psl\IO\Stream;

use Psl\IO;
use Psl\IO\Internal;

/**
 * @codeCoverageIgnore
 */
final class CloseSeekReadWriteHandle implements IO\CloseSeekReadWriteHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;
    use IO\WriteHandleConvenienceMethodsTrait;

    private IO\CloseSeekReadWriteHandleInterface $handle;

    /**
     * @param resource|object $stream
     *
     * @throws IO\Exception\BlockingException If unable to set the stream to non-blocking mode.
     */
    public function __construct(mixed $stream)
    {
        $this->handle = new Internal\ResourceHandle($stream, read: true, write: true, seek: true);
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
    public function read(?int $max_bytes = null, ?float $timeout = null): string
    {
        return $this->handle->read($max_bytes, $timeout);
    }

    /**
     * {@inheritDoc}
     */
    public function writeImmediately(string $bytes): int
    {
        return $this->handle->writeImmediately($bytes);
    }

    /**
     * {@inheritDoc}
     */
    public function write(string $bytes, ?float $timeout = null): int
    {
        return $this->handle->write($bytes, $timeout);
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

    /**
     * {@inheritDoc}
     */
    public function close(): void
    {
        $this->handle->close();
    }
}
