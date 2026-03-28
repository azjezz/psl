<?php

declare(strict_types=1);

namespace Psl\File\Internal;

use Override;
use Psl\Async;
use Psl\File;
use Psl\File\Lock;
use Psl\File\LockType;

abstract readonly class AbstractHandleWrapper implements File\HandleInterface
{
    public function __construct(
        private ResourceHandle $handle,
    ) {}

    /**
     * @inheritDoc
     */
    #[Override]
    public function getPath(): string
    {
        return $this->handle->getPath();
    }

    /**
     * @return int<0, max>
     *
     * @inheritDoc
     */
    #[Override]
    public function getSize(): int
    {
        return $this->handle->getSize();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function lock(
        LockType $type,
        Async\CancellationTokenInterface $cancellation = new Async\NullCancellationToken(),
    ): Lock {
        return $this->handle->lock($type, $cancellation);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function tryLock(LockType $type): Lock
    {
        return $this->handle->tryLock($type);
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
     * @inheritDoc
     */
    #[Override]
    public function isClosed(): bool
    {
        return $this->handle->isClosed();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function close(): void
    {
        $this->handle->close();
    }

    /**
     * @return resource|object|null
     *
     * @inheritDoc
     */
    #[Override]
    public function getStream(): mixed
    {
        return $this->handle->getStream();
    }
}
