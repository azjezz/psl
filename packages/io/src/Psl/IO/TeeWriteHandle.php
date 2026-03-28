<?php

declare(strict_types=1);

namespace Psl\IO;

use Override;
use Psl\Async;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;

use function substr;

/**
 * A write handle that duplicates all writes to two underlying write handles.
 *
 * Inspired by the Unix `tee` command, this handle forwards every byte written
 * to both {@see $first} and {@see $second}. The caller sees a single
 * {@see WriteHandleInterface} while data is transparently replicated.
 *
 * Because the two underlying handles may accept data at different rates,
 * {@see TeeWriteHandle} maintains an internal pending buffer for bytes that
 * {@see $first} has accepted but {@see $second} has not yet consumed. This
 * provides backpressure: {@see tryWrite()} returns 0 when the pending buffer
 * is non-empty and {@see $second} cannot drain it, preventing the caller from
 * writing more data until both handles are in sync.
 *
 * @api
 */
final class TeeWriteHandle implements WriteHandleInterface, CloseHandleInterface
{
    use WriteHandleConvenienceMethodsTrait;

    /**
     * Bytes accepted by the first handle but not yet written to the second.
     */
    private string $pendingForSecond = '';

    private bool $closed = false;

    /**
     * @param WriteHandleInterface $first The primary write handle.
     * @param WriteHandleInterface $second The secondary write handle that receives a copy of all data.
     */
    public function __construct(
        private readonly WriteHandleInterface $first,
        private readonly WriteHandleInterface $second,
    ) {}

    /**
     * Try to write bytes to both handles without blocking.
     *
     * The method first attempts to drain any pending bytes (previously accepted
     * by {@see $first} but not yet by {@see $second}) to the second handle. If
     * the pending buffer cannot be fully drained, returns 0 as backpressure to
     * signal the caller should retry later.
     *
     * When the pending buffer is empty, the method writes to {@see $first},
     * then writes the same accepted bytes to {@see $second}. If {@see $second}
     * accepts fewer bytes than {@see $first}, the remainder is stored in the
     * pending buffer for future draining.
     *
     * @throws Exception\AlreadyClosedException If the handle has been closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     *
     * @return int<0, max> The number of new bytes accepted, or 0 if backpressure is active.
     */
    #[Override]
    public function tryWrite(string $bytes): int
    {
        $this->assertHandleIsOpen();

        // Step 1: drain any pending buffer to $second
        if ($this->pendingForSecond !== '') {
            $drained = $this->second->tryWrite($this->pendingForSecond);
            if ($drained > 0) {
                $this->pendingForSecond = substr($this->pendingForSecond, $drained);
            }

            // Step 2: if pending buffer still not empty, return 0 (backpressure)
            if ($this->pendingForSecond !== '') {
                return 0;
            }
        }

        // Step 3: try write $bytes to $first
        $firstN = $this->first->tryWrite($bytes);

        // Step 4: if $first accepted nothing, return 0
        if ($firstN === 0) {
            return 0;
        }

        // Step 5: try write the same $firstN bytes to $second
        $slice = substr($bytes, 0, $firstN);
        $secondN = $this->second->tryWrite($slice);

        // Step 6: both in sync
        if ($secondN === $firstN) {
            return $firstN;
        }

        // Step 7: buffer the remainder for $second
        $this->pendingForSecond = substr($slice, $secondN);

        return $firstN;
    }

    /**
     * Write bytes to both handles, waiting if necessary.
     *
     * First drains any pending buffer to {@see $second} using {@see writeAll()}
     * (blocking until complete). Then writes to {@see $first} and, if any bytes
     * were accepted, writes the same bytes to {@see $second} using
     * {@see writeAll()} to guarantee both handles stay in sync.
     *
     * @throws Exception\AlreadyClosedException If the handle has been closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Async\Exception\CancelledException If the cancellation token is cancelled.
     *
     * @return int<0, max> The number of bytes written to the first handle.
     */
    #[Override]
    public function write(string $bytes, CancellationTokenInterface $cancellation = new NullCancellationToken()): int
    {
        $this->assertHandleIsOpen();

        // Step 1: drain pending buffer to $second (blocking)
        if ($this->pendingForSecond !== '') {
            $this->second->writeAll($this->pendingForSecond, $cancellation);
            $this->pendingForSecond = '';
        }

        // Step 2: write to $first
        $firstN = $this->first->write($bytes, $cancellation);

        // Step 3: if bytes were accepted, write them all to $second
        if ($firstN > 0) {
            $this->second->writeAll(substr($bytes, 0, $firstN), $cancellation);
        }

        return $firstN;
    }

    /**
     * Whether the handle has been closed.
     *
     * @throws void This method never throws.
     */
    #[Override]
    public function isClosed(): bool
    {
        return $this->closed;
    }

    /**
     * @codeCoverageIgnore
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Close the handle, closing both underlying handles and clearing the pending buffer.
     *
     * If either underlying handle implements {@see CloseHandleInterface}, it
     * will be closed. The pending buffer is discarded. After closing, all
     * operations will throw {@see Exception\AlreadyClosedException}.
     *
     * @throws Exception\RuntimeException If an error occurred while closing one of the handles.
     */
    #[Override]
    public function close(): void
    {
        $this->closed = true;
        $this->pendingForSecond = '';

        if ($this->first instanceof CloseHandleInterface) {
            $this->first->close();
        }

        if ($this->second instanceof CloseHandleInterface) {
            $this->second->close();
        }
    }

    /**
     * @throws Exception\AlreadyClosedException If the handle has been closed.
     */
    private function assertHandleIsOpen(): void
    {
        if ($this->closed) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }
    }
}
