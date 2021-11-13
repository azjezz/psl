<?php

declare(strict_types=1);

namespace Psl\File\Internal;

use Psl\File;
use Psl\File\Lock;
use Psl\File\LockType;

abstract class AbstractHandleWrapper implements File\HandleInterface
{
    public function __construct(private File\HandleInterface $handle)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getPath(): string
    {
        return $this->handle->getPath();
    }

    /**
     * {@inheritDoc}
     */
    public function getSize(): int
    {
        return $this->handle->getSize();
    }

    /**
     * {@inheritDoc}
     */
    public function lock(LockType $type): Lock
    {
        return $this->handle->lock($type);
    }

    /**
     * {@inheritDoc}
     */
    public function tryLock(LockType $type): Lock
    {
        return $this->handle->tryLock($type);
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
    public function close(): void
    {
        $this->handle->close();
    }

    /**
     * {@inheritDoc}
     */
    public function getStream(): mixed
    {
        return $this->handle->getStream();
    }
}
