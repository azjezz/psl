<?php

declare(strict_types=1);

namespace Psl\Compression;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\IO;

use function strlen;
use function substr;

/**
 * Decompressing write decorator.
 *
 * Accepts compressed data via write(), decompresses it via the given
 * decompressor, and writes the decompressed output to the inner handle.
 *
 * Decompressed output that cannot be written immediately is buffered internally.
 * Call {@see flush()} to finalize the decompression stream and drain all
 * remaining buffered data.
 */
final class DecompressingWriteHandle implements IO\BufferedWriteHandleInterface
{
    use IO\WriteHandleConvenienceMethodsTrait;

    private string $buffer = '';

    public function __construct(
        private readonly IO\WriteHandleInterface $inner,
        private readonly DecompressorInterface $decompressor,
    ) {}

    /**
     * Try to decompress and write data immediately, without waiting.
     *
     * All input bytes are consumed by the decompressor and buffered internally.
     * As much decompressed output as possible is drained to the inner handle
     * without blocking.
     *
     * @return int<0, max> The number of input bytes consumed (always strlen($bytes)).
     *
     * @throws Exception\RuntimeException If decompression fails.
     * @throws IO\Exception\AlreadyClosedException If the inner handle has been closed.
     * @throws IO\Exception\RuntimeException If the write to the inner handle fails.
     */
    #[Override]
    public function tryWrite(string $bytes): int
    {
        $this->buffer .= $this->decompressor->push($bytes);
        $this->drainTry();

        return strlen($bytes);
    }

    /**
     * Decompress and write data, waiting if necessary.
     *
     * All input bytes are consumed by the decompressor. The decompressed output
     * is written to the inner handle, waiting for the handle to become writable
     * if necessary. A single write is performed, which may not drain the
     * entire buffer.
     *
     * @return int<0, max> The number of input bytes consumed (always strlen($bytes)).
     *
     * @throws Exception\RuntimeException If decompression fails.
     * @throws IO\Exception\AlreadyClosedException If the inner handle has been closed.
     * @throws IO\Exception\RuntimeException If the write to the inner handle fails.
     * @throws CancelledException If the cancellation token is cancelled.
     */
    #[Override]
    public function write(string $bytes, CancellationTokenInterface $cancellation = new NullCancellationToken()): int
    {
        $this->buffer .= $this->decompressor->push($bytes);
        $this->drain($cancellation);

        return strlen($bytes);
    }

    /**
     * Finalize the decompression stream and drain all remaining buffered data.
     *
     * Calls {@see DecompressorInterface::finish()} to produce the final decompressed
     * output, then writes all buffered data to the inner handle.
     *
     * @throws Exception\RuntimeException If decompression finalization fails.
     * @throws IO\Exception\AlreadyClosedException If the inner handle has been closed.
     * @throws IO\Exception\RuntimeException If the write to the inner handle fails.
     * @throws CancelledException If the cancellation token is cancelled.
     */
    #[Override]
    public function flush(CancellationTokenInterface $cancellation = new NullCancellationToken()): void
    {
        $this->buffer .= $this->decompressor->finish();
        $this->drainAll($cancellation);
    }

    /**
     * Try to write as much of the buffer as possible without blocking.
     *
     * @throws IO\Exception\AlreadyClosedException If the inner handle has been closed.
     * @throws IO\Exception\RuntimeException If the write to the inner handle fails.
     */
    private function drainTry(): void
    {
        if ($this->buffer === '') {
            return;
        }

        $written = $this->inner->tryWrite($this->buffer);
        if ($written > 0) {
            $this->buffer = substr($this->buffer, $written);
        }
    }

    /**
     * Write some of the buffer, waiting if necessary.
     *
     * @throws IO\Exception\AlreadyClosedException If the inner handle has been closed.
     * @throws IO\Exception\RuntimeException If the write to the inner handle fails.
     * @throws CancelledException If the cancellation token is cancelled.
     */
    private function drain(CancellationTokenInterface $cancellation): void
    {
        if ($this->buffer === '') {
            return;
        }

        $written = $this->inner->write($this->buffer, $cancellation);
        if ($written > 0) {
            $this->buffer = substr($this->buffer, $written);
        }
    }

    /**
     * Write all the buffer, waiting as long as necessary.
     *
     * @throws IO\Exception\AlreadyClosedException If the inner handle has been closed.
     * @throws IO\Exception\RuntimeException If the write to the inner handle fails.
     * @throws CancelledException If the cancellation token is cancelled.
     */
    private function drainAll(CancellationTokenInterface $cancellation): void
    {
        if ($this->buffer === '') {
            return;
        }

        $this->inner->writeAll($this->buffer, $cancellation);
        $this->buffer = '';
    }
}
