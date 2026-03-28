<?php

declare(strict_types=1);

namespace Psl\IO;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;

use function strlen;

/**
 * A write handle that discards all data written to it.
 *
 * This handle behaves like `/dev/null`: every write operation succeeds
 * immediately and reports the full byte count, but the data is silently
 * discarded. This is useful when an API requires a {@see WriteHandleInterface}
 * but the caller does not need to capture the output.
 *
 * @api
 */
final class SinkWriteHandle implements BufferedWriteHandleInterface, CloseHandleInterface
{
    use WriteHandleConvenienceMethodsTrait;

    private bool $closed = false;

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
