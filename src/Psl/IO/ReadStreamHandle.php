<?php

declare(strict_types=1);

namespace Psl\IO;

use Override;
use Psl\DateTime\Duration;
use Psl\IO;

/**
 * @codeCoverageIgnore
 */
final class ReadStreamHandle implements StreamHandleInterface, ReadHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;

    private ReadHandleInterface&StreamHandleInterface $handle;

    /**
     * @param resource $stream
     */
    public function __construct(mixed $stream)
    {
        $this->handle = new Internal\ResourceHandle($stream, read: true, write: false, seek: false, close: false);
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
