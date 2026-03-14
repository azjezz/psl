<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;

/**
 * A buffered readable handle that provides higher-level reading methods
 * on top of {@see ReadHandleInterface}.
 */
interface BufferedReadHandleInterface extends ReadHandleInterface
{
    /**
     * Read a single byte from the handle.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation, or reached end of file.
     * @throws CancelledException If the cancellation token is cancelled.
     */
    public function readByte(CancellationTokenInterface $cancellation = new NullCancellationToken()): string;

    /**
     * Read until the current line terminator is found.
     *
     * Returns null if the end of file is reached before finding the line terminator.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws CancelledException If the cancellation token is cancelled.
     */
    public function readLine(CancellationTokenInterface $cancellation = new NullCancellationToken()): null|string;

    /**
     * Read until the specified suffix is seen.
     *
     * The trailing suffix is read (so won't be returned by other calls), but is not
     * included in the return value.
     *
     * Returns null if the suffix is not seen, even if there is other data.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws CancelledException If the cancellation token is cancelled.
     */
    public function readUntil(
        string $suffix,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): null|string;

    /**
     * Read until the specified suffix is seen, with a maximum number of bytes to read.
     *
     * The trailing suffix is read (so won't be returned by other calls), but is not
     * included in the return value.
     *
     * Returns null if the suffix is not seen before EOF.
     *
     * @param positive-int $max_bytes Maximum number of bytes to read before throwing OverflowException.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws CancelledException If the cancellation token is cancelled.
     * @throws Exception\OverflowException If $max_bytes is exceeded without finding the suffix.
     */
    public function readUntilBounded(
        string $suffix,
        int $max_bytes,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): null|string;
}
