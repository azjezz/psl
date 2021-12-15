<?php

declare(strict_types=1);

namespace Psl\IO;

/**
 * An `IO\Handle` that is readable.
 */
interface ReadHandleInterface extends HandleInterface
{
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
     * @return string the read data on success, or an empty string if the handle is not ready for read, or the end of file is reached.
     *
     * @see ReadStreamHandleInterface::read()
     * @see ReadStreamHandleInterface::readAll()
     */
    public function tryRead(?int $max_bytes = null): string;

    /**
     * Read from the handle, waiting for data if necessary.
     *
     * @param ?positive-int $max_bytes the maximum number of bytes to read
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\TimeoutException If $timeout is reached before being able to read from the handle.
     *
     * @return string the read data on success, or an empty string if the end of file is reached.
     *
     * Up to `$max_bytes` may be allocated in a buffer; large values may lead to
     * unnecessarily hitting the request memory limit.
     */
    public function read(?int $max_bytes = null, ?float $timeout = null): string;

    /**
     * Read until there is no more data to read.
     *
     * It is possible for this to never return, e.g. if called on a pipe or
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
    public function readAll(?int $max_bytes = null, ?float $timeout = null): string;

    /**
     * Read a fixed amount of data.
     *
     * It is possible for this to never return, e.g. if called on a pipe or
     * or socket which the other end keeps open forever. Set a timeout if you
     * do not want this to happen.
     *
     * @param positive-int $size the number of bytes to read.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\TimeoutException If $timeout is reached before being able to read from the handle.
     */
    public function readFixedSize(int $size, ?float $timeout = null): string;
}
