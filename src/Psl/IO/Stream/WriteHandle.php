<?php

declare(strict_types=1);

namespace Psl\IO\Stream;

use Psl\IO;
use Psl\IO\Internal;

/**
 * @codeCoverageIgnore
 */
final class WriteHandle implements WriteHandleInterface
{
    use IO\WriteHandleConvenienceMethodsTrait;

    private WriteHandleInterface $handle;

    /**
     * @param resource|object $stream
     */
    public function __construct(mixed $stream)
    {
        $this->handle = new Internal\ResourceHandle($stream, read: false, write: true, seek: false, close: false);
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
    public function getStream(): mixed
    {
        return $this->handle->getStream();
    }
}
