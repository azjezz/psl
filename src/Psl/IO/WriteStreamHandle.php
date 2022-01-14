<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\IO;

/**
 * @codeCoverageIgnore
 */
final class WriteStreamHandle implements WriteStreamHandleInterface
{
    use IO\WriteHandleConvenienceMethodsTrait;

    private WriteStreamHandleInterface $handle;

    /**
     * @param resource $stream
     */
    public function __construct(mixed $stream)
    {
        $this->handle = new Internal\ResourceHandle($stream, read: false, write: true, seek: false, close: false);
    }

    /**
     * {@inheritDoc}
     */
    public function tryWrite(string $bytes): int
    {
        return $this->handle->tryWrite($bytes);
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
    public function getStream(): mixed
    {
        return $this->handle->getStream();
    }
}
