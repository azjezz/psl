<?php

declare(strict_types=1);

namespace Psl\Encoding\QuotedPrintable;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO;

use function str_ends_with;
use function strlen;
use function strpos;
use function substr;

/**
 * A write handle that accepts raw text and writes quoted-printable encoded output to an inner handle.
 *
 * Buffers input until complete lines (terminated by \n) are available,
 * encodes each line via {@see encode_line()}, and writes to the inner handle with "\r\n" line endings.
 */
final class EncodingWriteHandle implements IO\WriteHandleInterface
{
    use IO\WriteHandleConvenienceMethodsTrait;

    private string $buffer = '';
    private bool $firstLine = true;

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
     * Flush any remaining buffered data through the encoder to the inner handle.
     */
    public function flush(): void
    {
        if ($this->buffer !== '') {
            $line = str_ends_with($this->buffer, "\r") ? substr($this->buffer, 0, -1) : $this->buffer;

            if (!$this->firstLine) {
                $this->handle->writeAll("\r\n");
            }

            $this->handle->writeAll(namespace\encode_line($line));
            $this->buffer = '';
            $this->firstLine = false;
        }
    }

    private function drainCompleteLines(): void
    {
        while (false !== ($pos = strpos($this->buffer, "\n"))) {
            $line = substr($this->buffer, 0, $pos);
            $line = str_ends_with($line, "\r") ? substr($line, 0, -1) : $line;
            $this->buffer = substr($this->buffer, $pos + 1);

            if (!$this->firstLine) {
                $this->handle->writeAll("\r\n");
            }

            $this->handle->writeAll(namespace\encode_line($line));
            $this->firstLine = false;
        }
    }
}
