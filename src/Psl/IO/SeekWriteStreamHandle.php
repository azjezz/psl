<?php

declare(strict_types=1);

namespace Psl\IO;

use Override;
use Psl\DateTime\Duration;
use Psl\IO;

/**
 * @codeCoverageIgnore
 */
final class SeekWriteStreamHandle implements StreamHandleInterface, WriteHandleInterface, SeekHandleInterface
{
    use IO\WriteHandleConvenienceMethodsTrait;

    private StreamHandleInterface&WriteHandleInterface&SeekHandleInterface $handle;

    /**
     * @param resource $stream
     */
    public function __construct(mixed $stream)
    {
        $this->handle = new Internal\ResourceHandle($stream, read: false, write: true, seek: true, close: false);
    }

    /**
     * @return int<0, max>
     *
     * @inheritDoc
     */
    #[Override]
    public function tryWrite(string $bytes): int
    {
        return $this->handle->tryWrite($bytes);
    }

    /**
     * @return int<0, max>
     *
     * @inheritDoc
     */
    #[Override]
    public function write(string $bytes, null|Duration $timeout = null): int
    {
        return $this->handle->write($bytes, $timeout);
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
