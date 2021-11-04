<?php

declare(strict_types=1);

namespace Psl\IO\Stream;

use Psl\IO;
use Psl\IO\Internal;

/**
 * @codeCoverageIgnore
 */
final class WriteHandle implements IO\WriteHandleInterface
{
    use IO\WriteHandleConvenienceMethodsTrait;

    private IO\WriteHandleInterface $handle;

    /**
     * @param resource|object $stream
     *
     * @throws IO\Exception\BlockingException If unable to set the stream to non-blocking mode.
     */
    public function __construct(mixed $stream)
    {
        $this->handle = new Internal\ResourceHandle($stream, read: false, write: true, seek: false);
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
    public function write(string $bytes, ?int $timeout_ms = null): int
    {
        return $this->handle->write($bytes, $timeout_ms);
    }
}
