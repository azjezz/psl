<?php

declare(strict_types=1);

namespace Psl\IO\Stream;

use Psl\IO;
use Psl\IO\Internal;

/**
 * @codeCoverageIgnore
 */
final class SeekReadHandle implements SeekReadHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;

    private SeekReadHandleInterface $handle;

    /**
     * @param resource|object $stream
     */
    public function __construct(mixed $stream)
    {
        $this->handle = new Internal\ResourceHandle($stream, read: true, write: false, seek: true, close: false);
    }

    /**
     * {@inheritDoc}
     */
    public function tryRead(?int $max_bytes = null): string
    {
        return $this->handle->tryRead($max_bytes);
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
    public function getStream(): mixed
    {
        return $this->handle->getStream();
    }
}
