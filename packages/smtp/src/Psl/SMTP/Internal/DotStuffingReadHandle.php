<?php

declare(strict_types=1);

namespace Psl\SMTP\Internal;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\IO;

use function str_ends_with;
use function strlen;
use function substr;

/**
 * Wraps a serialized message stream, escaping leading dots and appending the final ".\r\n" terminator.
 *
 * Per RFC 5321 §4.5.2, any line beginning with "." must have an additional "." prepended
 * (dot-stuffing). The end of mail data is indicated by "\r\n.\r\n".
 *
 * @internal
 */
final class DotStuffingReadHandle implements IO\ReadHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;

    private const int CHUNK_SIZE = 8192;

    private string $buffer = '';
    private bool $atLineStart = true;
    private bool $innerExhausted = false;
    private bool $finished = false;

    public function __construct(
        private readonly IO\ReadHandleInterface $inner,
    ) {}

    /**
     * @throws IO\Exception\RuntimeException If reading from the inner handle fails.
     */
    public function tryRead(null|int $maxBytes = null): string
    {
        if ($this->finished) {
            return '';
        }

        if ($this->buffer === '' && !$this->innerExhausted) {
            $chunk = $this->inner->tryRead(self::CHUNK_SIZE);
            $this->processChunk($chunk);
        }

        if ($this->buffer === '' && $this->innerExhausted) {
            $this->finished = true;
            $suffix = '';
            if (!$this->atLineStart) {
                $suffix = "\r\n";
            }

            return $suffix . ".\r\n";
        }

        $max = $maxBytes ?? strlen($this->buffer);
        $result = substr($this->buffer, 0, $max);
        $this->buffer = substr($this->buffer, strlen($result));

        if ($result !== '') {
            $this->atLineStart = str_ends_with($result, "\n");
        }

        return $result;
    }

    /**
     * @throws IO\Exception\RuntimeException If reading from the inner handle fails.
     * @throws CancelledException
     */
    public function read(
        null|int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        if ($this->finished) {
            return '';
        }

        if ($this->buffer === '' && !$this->innerExhausted) {
            $chunk = $this->inner->read(self::CHUNK_SIZE, $cancellation);
            $this->processChunk($chunk);
        }

        if ($this->buffer === '' && $this->innerExhausted) {
            $this->finished = true;
            $suffix = '';
            if (!$this->atLineStart) {
                $suffix = "\r\n";
            }

            return $suffix . ".\r\n";
        }

        $max = $maxBytes ?? strlen($this->buffer);
        $result = substr($this->buffer, 0, $max);
        $this->buffer = substr($this->buffer, strlen($result));

        if ($result !== '') {
            $this->atLineStart = str_ends_with($result, "\n");
        }

        return $result;
    }

    public function reachedEndOfDataSource(): bool
    {
        return $this->finished;
    }

    private function processChunk(string $chunk): void
    {
        if ($chunk === '') {
            if ($this->inner->reachedEndOfDataSource()) {
                $this->innerExhausted = true;
            }

            return;
        }

        $output = '';
        $length = strlen($chunk);
        for ($i = 0; $i < $length; $i++) {
            $byte = $chunk[$i];
            if ($byte === '.' && $this->atLineStart) {
                $output .= '.';
            }

            $output .= $byte;
            $this->atLineStart = $byte === "\n";
        }

        $this->buffer .= $output;
    }
}
