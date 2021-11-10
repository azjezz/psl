<?php

declare(strict_types=1);

namespace Psl\IO\Stream;

use Psl\IO;
use Psl\IO\Internal;

/**
 * @codeCoverageIgnore
 */
final class ReadWriteHandle implements IO\ReadWriteHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;
    use IO\WriteHandleConvenienceMethodsTrait;

    private IO\ReadWriteHandleInterface $handle;

    /**
     * @param resource|object $stream
     */
    public function __construct(mixed $stream)
    {
        $this->handle = new Internal\ResourceHandle($stream, read: true, write: true, seek: false);
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
}
