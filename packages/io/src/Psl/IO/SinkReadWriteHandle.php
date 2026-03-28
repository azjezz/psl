<?php

declare(strict_types=1);

namespace Psl\IO;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;

use function strlen;

/**
 * A read-write handle that discards all written data and always reads as empty/EOF.
 *
 * This handle combines the behaviour of `/dev/null` for both reading and
 * writing: every write succeeds immediately and reports the full byte count,
 * while every read returns an empty string and signals end-of-data. This is
 * useful when an API requires both {@see ReadHandleInterface} and
 * {@see WriteHandleInterface} but the caller does not need to capture the
 * output or provide any input.
 *
 * @api
 */
final class SinkReadWriteHandle implements ReadHandleInterface, BufferedWriteHandleInterface, CloseHandleInterface
{
    use ReadHandleConvenienceMethodsTrait;
    use WriteHandleConvenienceMethodsTrait;

    private bool $closed = false;

    /**
     * Indicates whether the cursor has reached the end of the data source (EOF).
     *
     * This handle is always at EOF because it contains no data.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function reachedEndOfDataSource(): bool
    {
        $this->assertHandleIsOpen();

        return true;
    }

    /**
     * Try to read from the handle immediately, without waiting.
     *
     * Always returns an empty string because this handle contains no data.
     *
     * @param ?positive-int $maxBytes the maximum number of bytes to read
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     *
     * @return string always an empty string.
     */
    #[Override]
    public function tryRead(null|int $maxBytes = null): string
    {
        $this->assertHandleIsOpen();

        return '';
    }

    /**
     * Read from the handle, waiting for data if necessary.
     *
     * Always returns an empty string because this handle contains no data.
     *
     * @param ?positive-int $maxBytes the maximum number of bytes to read
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     *
     * @return string always an empty string.
     */
    #[Override]
    public function read(
        null|int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        return $this->tryRead($maxBytes);
    }

    /**
     * Try to write to the handle immediately, without waiting.
     *
     * All bytes are accepted and silently discarded.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     *
     * @return int<0, max> the number of bytes written on success.
     */
    #[Override]
    public function tryWrite(string $bytes): int
    {
        $this->assertHandleIsOpen();

        return strlen($bytes);
    }

    /**
     * Write data, waiting if necessary.
     *
     * All bytes are accepted and silently discarded.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     *
     * @return int<0, max> the number of bytes written, which is always the full length of the input.
     */
    #[Override]
    public function write(string $bytes, CancellationTokenInterface $cancellation = new NullCancellationToken()): int
    {
        return $this->tryWrite($bytes);
    }

    public function flush(CancellationTokenInterface $cancellation = new NullCancellationToken()): void
    {
        $this->assertHandleIsOpen();
    }

    /**
     * Check whether the handle has been closed.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function isClosed(): bool
    {
        return $this->closed;
    }

    /**
     * Close the handle.
     *
     * @psalm-external-mutation-free
     */
    #[Override]
    public function close(): void
    {
        $this->closed = true;
    }

    /**
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     *
     * @psalm-mutation-free
     */
    private function assertHandleIsOpen(): void
    {
        if ($this->closed) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function __destruct()
    {
        $this->close();
    }
}
