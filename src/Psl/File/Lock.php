<?php

declare(strict_types=1);

namespace Psl\File;

use Closure;

final class Lock
{
    private bool $released = false;

    /**
     * @param (Closure(): void) $release
     *
     * @internal use HandleInterface::lock() to create a lock.
     */
    public function __construct(
        public readonly LockType $type,
        private Closure $releaseCallback,
    ) {
    }

    /**
     * Release the lock.
     */
    public function release(): void
    {
        if ($this->released) {
            return;
        }

        ($this->releaseCallback)();
        $this->released = true;
    }

    public function __destruct()
    {
        $this->release();
    }
}
