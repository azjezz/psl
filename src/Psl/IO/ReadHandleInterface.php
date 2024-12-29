<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\DateTime\Duration;

/**
 * An `IO\Handle` that is readable.
 */
interface ReadHandleInterface extends HandleInterface
{
    /**
     * Indicates whether the cursor has reached the end of the data source (EOF).
     *
     * This method returns `true` if an attempt to read beyond the end of the data source has been made,
     * indicating that no more data is available for reading. Conversely, it returns `false` if the
     * end of the data source has not been reached or if no data exists but no attempt to read has been made.
     *
     * It is important to note that a return value of `false` does not guarantee that subsequent calls to
     * `read()` or `tryRead()` will return data. This could occur in situations where the handle is completely empty,
     * and no read operations have been performed yet, or if the handle is not ready for reading. In such cases,
     * calls to `read()` or `tryRead()` might return an empty string, reflecting the absence of available data
     * at the time of the call.
     *
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\AlreadyClosedException If the handle has already been closed.
     *
     * @return bool `true` if the cursor is at the end of the data source, otherwise `false`.
     */
    public function reachedEndOfDataSource(): bool;

    /**
     * Try to read from the handle immediately, without waiting.
     *
     * Up to `$max_bytes` may be allocated in a buffer; large values may lead to
     * unnecessarily hitting the request memory limit.
     *
     * @param ?positive-int $max_bytes the maximum number of bytes to read
     *
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     *
     * @return string the read data on success, or an empty string if the handle is not ready for read, or the end of data source is reached.
     *
     * @see ReadStreamHandleInterface::read()
     * @see ReadStreamHandleInterface::readAll()
     */
    public function tryRead(null|int $max_bytes = null): string;

    /**
     * Read from the handle, waiting for data if necessary.
     *
     * @param ?positive-int $max_bytes the maximum number of bytes to read
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\TimeoutException If $timeout is reached before being able to read from the handle.
     *
     * @return string the read data on success, or an empty string if the end of data source is reached.
     *
     * Up to `$max_bytes` may be allocated in a buffer; large values may lead to
     * unnecessarily hitting the request memory limit.
     */
    public function read(null|int $max_bytes = null, null|Duration $timeout = null): string;

    /**
     * Read until there is no more data to read.
     *
     * It is possible for this to never return, e.g. if called on a pipe
     * or socket which the other end keeps open forever. Set a timeout if you
     * do not want this to happen.
     *
     * Up to `$max_bytes` may be allocated in a buffer; large values may lead to
     * unnecessarily hitting the request memory limit.
     *
     * @param ?positive-int $max_bytes the maximum number of bytes to read
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\TimeoutException If $timeout is reached before being able to read from the handle.
     */
    public function readAll(null|int $max_bytes = null, null|Duration $timeout = null): string;

    /**
     * Read a fixed amount of data.
     *
     * It is possible for this to never return, e.g. if called on a pipe
     * or socket which the other end keeps open forever. Set a timeout if you
     * do not want this to happen.
     *
     * @param positive-int $size the number of bytes to read.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\TimeoutException If $timeout is reached before being able to read from the handle.
     */
    public function readFixedSize(int $size, null|Duration $timeout = null): string;
}
