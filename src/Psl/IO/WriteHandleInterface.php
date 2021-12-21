<?php

declare(strict_types=1);

namespace Psl\IO;

/**
 * An interface for a writable Handle.
 */
interface WriteHandleInterface extends HandleInterface
{
    /**
     * Try to write to the handle immediately, without waiting.
     *
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     *
     * @return int<0, max> the number of bytes written on success, which may be 0.
     *
     * @see WriteHandleInterface::write()
     * @see WriteHandleInterface::writeAll()
     */
    public function tryWrite(string $bytes): int;

    /**
     * Write data, waiting if necessary.
     *
     * It is possible for the write to *partially* succeed - check the return
     * value and call again if needed.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\TimeoutException If reached timeout before completing the operation.
     *
     * @return int<0, max> the number of bytes written, which may be less than the length of input string.
     */
    public function write(string $bytes, ?float $timeout = null): int;

    /**
     * Write all of the requested data.
     *
     * A wrapper around {@see WriteHandleInterface::write()} that will:
     * - do multiple writes if necessary to write the entire provided buffer
     * - throws {@see Exception\RuntimeException} if it is not possible to write all the requested data
     *
     * It is possible for this to never return, e.g. if called on a pipe or
     * or socket which the other end keeps open forever. Set a timeout if you
     * do not want this to happen.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\TimeoutException If reached timeout before completing the operation.
     */
    public function writeAll(string $bytes, ?float $timeout = null): void;
}
