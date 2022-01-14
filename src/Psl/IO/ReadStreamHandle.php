<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\IO;

/**
 * @codeCoverageIgnore
 */
final class ReadStreamHandle implements ReadStreamHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;

    private ReadStreamHandleInterface $handle;

    /**
     * @param resource $stream
     */
    public function __construct(mixed $stream)
    {
        $this->handle = new Internal\ResourceHandle($stream, read: true, write: false, seek: false, close: false);
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
    public function getStream(): mixed
    {
        return $this->handle->getStream();
    }
}
