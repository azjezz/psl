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
 * Lazy compressing read decorator.
 *
 * Wraps a raw data handle and returns compressed output on read.
 * Reads chunks from the inner handle, compresses each via the given
 * compressor, and buffers the result.
 */
final class CompressingReadHandle implements IO\ReadHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;

    private string $buffer = '';
    private bool $eof = false;

    /**
     * @param positive-int $chunkSize Number of bytes to read from the inner handle per iteration.
     */
    public function __construct(
        private readonly IO\ReadHandleInterface $inner,
        private readonly CompressorInterface $compressor,
        private readonly int $chunkSize = 8192,
    ) {}

    /**
     * @param ?positive-int $maxBytes the maximum number of bytes to read
     *
     * @throws Exception\RuntimeException If a compression fails.
     * @throws IO\Exception\AlreadyClosedException If the handle has been already closed.
     * @throws IO\Exception\RuntimeException If an error occurred during the operation.
     */
    #[Override]
    public function tryRead(null|int $maxBytes = null): string
    {
        while ($this->buffer === '' && !$this->eof) {
            $chunk = $this->inner->tryRead($this->chunkSize);
            if ($chunk === '' && !$this->inner->reachedEndOfDataSource()) {
                break;
            }

            $this->processChunk($chunk);
        }

        return $this->consumeBuffer($maxBytes);
    }

    /**
     * @param ?positive-int $maxBytes the maximum number of bytes to read
     *
     * @throws Exception\RuntimeException If a compression fails.
     * @throws IO\Exception\AlreadyClosedException If the handle has been already closed.
     * @throws IO\Exception\RuntimeException If an error occurred during the operation.
     * @throws CancelledException If the cancellation token is cancelled.
     */
    #[Override]
    public function read(
        null|int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        while ($this->buffer === '' && !$this->eof) {
            $chunk = $this->inner->read($this->chunkSize, $cancellation);
            if ($chunk === '' && !$this->inner->reachedEndOfDataSource()) {
                break;
            }

            $this->processChunk($chunk);
        }

        return $this->consumeBuffer($maxBytes);
    }

    /**
     * Whether all data from the inner handle has been read and compressed.
     *
     * Returns true only after the inner handle reaches EOF and the compressor
     * has been finalized, with all buffered output consumed.
     */
    #[Override]
    public function reachedEndOfDataSource(): bool
    {
        return $this->eof && $this->buffer === '';
    }

    /**
     * Process a chunk from the inner handle through the compressor.
     *
     * An empty chunk signals EOF on the inner handle, triggering finalization
     * of the compression stream.
     *
     * @throws Exception\RuntimeException If the compression operation fails.
     */
    private function processChunk(string $chunk): void
    {
        if ($chunk === '') {
            $this->buffer .= $this->compressor->finish();
            $this->eof = true;

            return;
        }

        $this->buffer .= $this->compressor->push($chunk);
    }

    /**
     * Consume up to $maxBytes from the internal buffer, or all of it if null.
     *
     * @param ?positive-int $maxBytes
     */
    private function consumeBuffer(null|int $maxBytes): string
    {
        if ($this->buffer === '') {
            return '';
        }

        if ($maxBytes === null) {
            $result = $this->buffer;
            $this->buffer = '';
        } else {
            $result = substr($this->buffer, 0, $maxBytes);
            $this->buffer = substr($this->buffer, strlen($result));
        }

        return $result;
    }
}
