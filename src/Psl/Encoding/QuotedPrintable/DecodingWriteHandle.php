<?php

declare(strict_types=1);

namespace Psl\Encoding\QuotedPrintable;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO;

use function quoted_printable_decode;
use function str_ends_with;
use function strlen;
use function strpos;
use function substr;

/**
 * A write handle that accepts quoted-printable encoded bytes and writes decoded output to an inner handle.
 *
 * Buffers input until complete lines (terminated by \n) are available,
 * handles soft-break continuations, then decodes and writes to the inner handle.
 */
final class DecodingWriteHandle implements IO\WriteHandleInterface
{
    use IO\WriteHandleConvenienceMethodsTrait;

    private string $buffer = '';
    private string $accumulated = '';
    private bool $hardBreakPending = false;

    public function __construct(
        private readonly IO\WriteHandleInterface $handle,
    ) {}

    public function tryWrite(string $bytes): int
    {
        $length = strlen($bytes);
        $this->buffer .= $bytes;
        $this->drainCompleteLines();
        return $length;
    }

    public function write(string $bytes, CancellationTokenInterface $cancellation = new NullCancellationToken()): int
    {
        return $this->tryWrite($bytes);
    }

    /**
     * Flush any remaining buffered data through the decoder to the inner handle.
     */
    public function flush(): void
    {
        $this->accumulated .= str_ends_with($this->buffer, "\r") ? substr($this->buffer, 0, -1) : $this->buffer;
        $this->buffer = '';

        if ($this->accumulated !== '') {
            if ($this->hardBreakPending) {
                $this->handle->writeAll("\r\n");
            }

            $this->handle->writeAll(quoted_printable_decode($this->accumulated));
            $this->accumulated = '';
            $this->hardBreakPending = false;
        }
    }

    private function drainCompleteLines(): void
    {
        while (false !== ($pos = strpos($this->buffer, "\n"))) {
            $line = substr($this->buffer, 0, $pos);
            $line = str_ends_with($line, "\r") ? substr($line, 0, -1) : $line;
            $this->buffer = substr($this->buffer, $pos + 1);

            if (str_ends_with($line, '=')) {
                /** @var non-negative-int $len */
                $len = strlen($line) - 1;
                $this->accumulated .= substr($line, 0, $len);

                continue;
            }

            $this->accumulated .= $line;

            // @codeCoverageIgnoreStart
            if ($this->hardBreakPending) {
                $this->handle->writeAll("\r\n");
            }

            // @codeCoverageIgnoreEnd

            $this->handle->writeAll(quoted_printable_decode($this->accumulated));
            $this->accumulated = '';
            $this->hardBreakPending = true;
        }
    }
}
