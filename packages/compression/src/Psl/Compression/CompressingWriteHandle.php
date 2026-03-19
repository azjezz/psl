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
 * Compressing write decorator.
 *
 * Accepts raw data via write(), compresses it via the given compressor,
 * and writes the compressed output to the inner handle.
 *
 * Compressed output that cannot be written immediately is buffered internally.
 * Call {@see flush()} to finalize the compression stream and drain all
 * remaining buffered data.
 */
final class CompressingWriteHandle implements IO\BufferedWriteHandleInterface
{
    use IO\WriteHandleConvenienceMethodsTrait;

    private string $buffer = '';

    public function __construct(
        private readonly IO\WriteHandleInterface $inner,
        private readonly CompressorInterface $compressor,
    ) {}

    /**
     * Try to compress and write data immediately, without waiting.
     *
     * All input bytes are consumed by the compressor and buffered internally.
     * As much compressed output as possible is drained to the inner handle
     * without blocking.
     *
     * @return int<0, max> The number of input bytes consumed (always strlen($bytes)).
     *
     * @throws Exception\RuntimeException If compression fails.
     * @throws IO\Exception\AlreadyClosedException If the inner handle has been closed.
     * @throws IO\Exception\RuntimeException If the write to the inner handle fails.
     */
    #[Override]
    public function tryWrite(string $bytes): int
    {
        $this->buffer .= $this->compressor->push($bytes);
        $this->drainTry();

        return strlen($bytes);
    }

    /**
     * Compress and write data, waiting if necessary.
     *
     * All input bytes are consumed by the compressor. The compressed output
     * is written to the inner handle, waiting for the handle to become writable
     * if necessary. A single write is performed, which may not drain the
     * entire buffer.
     *
     * @return int<0, max> The number of input bytes consumed (always strlen($bytes)).
     *
     * @throws Exception\RuntimeException If compression fails.
     * @throws IO\Exception\AlreadyClosedException If the inner handle has been closed.
     * @throws IO\Exception\RuntimeException If the write to the inner handle fails.
     * @throws CancelledException If the cancellation token is cancelled.
     */
    #[Override]
    public function write(string $bytes, CancellationTokenInterface $cancellation = new NullCancellationToken()): int
    {
        $this->buffer .= $this->compressor->push($bytes);
        $this->drain($cancellation);

        return strlen($bytes);
    }

    /**
     * Finalize the compression stream and drain all remaining buffered data.
     *
     * Calls {@see CompressorInterface::finish()} to produce the final compressed
     * output, then writes all buffered data to the inner handle.
     *
     * @throws Exception\RuntimeException If compression finalization fails.
     * @throws IO\Exception\AlreadyClosedException If the inner handle has been closed.
     * @throws IO\Exception\RuntimeException If the write to the inner handle fails.
     * @throws CancelledException If the cancellation token is cancelled.
     */
    #[Override]
    public function flush(CancellationTokenInterface $cancellation = new NullCancellationToken()): void
    {
        $this->buffer .= $this->compressor->finish();
        $this->drainAll($cancellation);
    }

    /**
     * Try to write as much of the buffer as possible without blocking.
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
