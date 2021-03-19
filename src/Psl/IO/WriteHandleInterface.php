<?php

declare(strict_types=1);

namespace Psl\IO;

/**
 * An interface for a writable Handle.
 */
interface WriteHandleInterface extends HandleInterface
{
    /**
     * An immediate unordered write.
     *
     * @returns int the number of bytes written on success
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the write would block.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     */
    public function write(string $bytes): int;

    /**
     * Flush the handle.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If unable to flush the handle.
     */
    public function flush(): void;
}
