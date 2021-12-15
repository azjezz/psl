<?php

declare(strict_types=1);

namespace Psl\File;

use Psl\IO;

interface HandleInterface extends IO\CloseSeekStreamHandleInterface
{
    /**
     * Gets the path to the file.
     */
    public function getPath(): string;

    /**
     * Get the size of the file.
     *
     * @throws IO\Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     */
    public function getSize(): int;

    /**
     * Get a shared or exclusive lock on the file.
     *
     * This will block until it acquires the lock, which may be forever.
     *
     * This involves a blocking syscall; async code will not execute while
     * waiting for a lock.
     *
     * @throws IO\Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     *
     * Example:
     *
     * ```php
     * $lock = $file->lock(LockType::SHARED);
     * // lock has been acquired.
     * $lock->release();
     * ```
     */
    public function lock(LockType $type): Lock;

    /**
     * Immediately get a shared or exclusive lock on a file, or throw.
     *
     * @throws IO\Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\AlreadyLockedException if `lock()` would block.
     *
     * Example:
     *
     * ```php
     * try {
     *   $lock = $file->tryLock(LockType::SHARED);
     *   // lock has been acquired.
     *   $lock->release();
     * } catch(AlreadyLockedException) {
     *   // cannot acquire lock.
     * }
     * ```
     */
    public function tryLock(LockType $type): Lock;
}
