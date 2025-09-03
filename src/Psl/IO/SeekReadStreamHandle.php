<?php

declare(strict_types=1);

namespace Psl\IO;

use Override;
use Psl\DateTime\Duration;
use Psl\IO;

/**
 * @codeCoverageIgnore
 */
final class SeekReadStreamHandle implements StreamHandleInterface, ReadHandleInterface, SeekHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;

    private StreamHandleInterface&ReadHandleInterface&SeekHandleInterface $handle;

    /**
     * @param resource $stream
     */
    public function __construct(mixed $stream)
    {
        $this->handle = new Internal\ResourceHandle($stream, read: true, write: false, seek: true, close: false);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function reachedEndOfDataSource(): bool
    {
        return $this->handle->reachedEndOfDataSource();
    }

    /**
     * @param ?positive-int $max_bytes the maximum number of bytes to read
     *
     * @inheritDoc
     */
    #[Override]
    public function tryRead(null|int $max_bytes = null): string
    {
        return $this->handle->tryRead($max_bytes);
    }

    /**
     * @param ?positive-int $max_bytes the maximum number of bytes to read
     *
     * @inheritDoc
     */
    #[Override]
    public function read(null|int $max_bytes = null, null|Duration $timeout = null): string
    {
        return $this->handle->read($max_bytes, $timeout);
    }

    /**
     * @param int<0, max> $offset
     *
     * @inheritDoc
     */
    #[Override]
    public function seek(int $offset): void
    {
        $this->handle->seek($offset);
    }

    /**
     * @return int<0, max>
     *
     * @inheritDoc
     */
    #[Override]
    public function tell(): int
    {
        return $this->handle->tell();
    }

    /**
     * @return resource|null
     *
     * @inheritDoc
     */
    #[Override]
    public function getStream(): mixed
    {
        return $this->handle->getStream();
    }
}
