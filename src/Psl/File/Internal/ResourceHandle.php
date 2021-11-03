<?php

declare(strict_types=1);

namespace Psl\File\Internal;

use Psl\Async;
use Psl\File;
use Psl\File\Lock;
use Psl\File\LockType;
use Psl\IO;
use Psl\IO\Exception;
use Psl\Str;

use function error_get_last;
use function flock;
use function fseek;

use const LOCK_EX;
use const LOCK_NB;
use const LOCK_SH;
use const LOCK_UN;
use const SEEK_END;

/**
 * @internal
 */
final class ResourceHandle extends IO\Internal\ResourceHandle implements File\ReadWriteHandleInterface
{
    private string $path;

    /**
     * @param resource|object $resource
     *
     * @throws Exception\BlockingException If unable to set the handle resource to non-blocking mode.
     */
    public function __construct(string $path, mixed $resource, bool $read, bool $write)
    {
        parent::__construct($resource, $read, $write, seek: true);

        $this->path = $path;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritDoc}
     */
    public function getSize(): int
    {
        if (null === $this->resource) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }

        // @codeCoverageIgnoreStart
        try {
            $position = $this->tell();
        } catch (IO\Exception\RuntimeException $previous) {
            throw new File\Exception\RuntimeException($previous->getMessage(), previous: $previous);
        }

        /** @psalm-suppress PossiblyInvalidArgument */
        $result = @fseek($this->resource, 0, SEEK_END);
        if ($result === -1) {
            $error = error_get_last();

            throw new File\Exception\RuntimeException($error['message'] ?? 'unknown error.');
        }

        try {
            $size = $this->tell();
            $this->seek($position);
        } catch (IO\Exception\RuntimeException $previous) {
            throw new File\Exception\RuntimeException($previous->getMessage(), previous: $previous);
        }
        // @codeCoverageIgnoreEnd

        return $size;
    }

    /**
     * {@inheritDoc}
     *
     * @codeCoverageIgnore
     */
    public function lock(LockType $type): Lock
    {
        while (true) {
            try {
                return $this->tryLock($type);
            } catch (File\Exception\AlreadyLockedException) {
                Async\later();
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function tryLock(LockType $type): Lock
    {
        if (null === $this->resource) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }

        $operations = LOCK_NB | ($type === LockType::EXCLUSIVE ? LOCK_EX : LOCK_SH);
        /** @psalm-suppress PossiblyInvalidArgument */
        $success = @flock($this->resource, $operations, $would_block);
        // @codeCoverageIgnoreStart
        if ($would_block) {
            throw new File\Exception\AlreadyLockedException();
        }

        if (!$success) {
            throw new File\Exception\RuntimeException(Str\format(
                'Could not acquire %s lock for "%s".',
                $type === LockType::EXCLUSIVE ? 'exclusive' : 'shared',
                $this->getPath(),
            ));
        }

        return new Lock($type, function (): void {
            if (null === $this->resource) {
                // while closing a handle should unlock it, that is not always the case.
                // therefore, we should require users to explicitly release the lock before closing the handle.
                throw new Exception\AlreadyClosedException('Handle was closed before releasing the lock.');
            }

            /** @psalm-suppress PossiblyInvalidArgument */
            if (!@flock($this->resource, LOCK_UN)) {
                throw new File\Exception\RuntimeException(Str\format(
                    'Could not release lock for "%s".',
                    $this->getPath(),
                ));
            }
        });
        // @codeCoverageIgnoreEnd
    }
}
