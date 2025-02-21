<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\DateTime\Duration;
use Psl\IO;

/**
 * @codeCoverageIgnore
 */
final class SeekWriteStreamHandle implements SeekWriteStreamHandleInterface
{
    use IO\WriteHandleConvenienceMethodsTrait;

    private SeekWriteStreamHandleInterface $handle;

    /**
     * @param resource $stream
     */
    public function __construct(mixed $stream)
    {
        $this->handle = new Internal\ResourceHandle($stream, read: false, write: true, seek: true, close: false);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function tryWrite(string $bytes): int
    {
        return $this->handle->tryWrite($bytes);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function write(string $bytes, null|Duration $timeout = null): int
    {
        return $this->handle->write($bytes, $timeout);
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
    public function getStream(): mixed
    {
        return $this->handle->getStream();
    }
}
