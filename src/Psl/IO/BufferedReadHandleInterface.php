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
     * Read until a newline ("\n") is found.
     *
     * The trailing "\n" is consumed but not included in the return value.
     * If the line ends with "\r\n", the trailing "\r" is also stripped.
     *
     * Returns null if the end of file is reached before finding a newline.
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
     * @param positive-int $maxBytes Maximum number of bytes to read before throwing OverflowException.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws CancelledException If the cancellation token is cancelled.
     * @throws Exception\OverflowException If $maxBytes is exceeded without finding the suffix.
     */
    public function readUntilBounded(
        string $suffix,
        int $maxBytes,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): null|string;
}
