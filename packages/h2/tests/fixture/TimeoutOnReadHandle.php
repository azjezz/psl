<?php

declare(strict_types=1);

namespace Psl\H2\Tests\Fixture;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO;

use function strlen;
use function substr;

final class TimeoutOnReadHandle implements IO\ReadHandleInterface, IO\WriteHandleInterface
{
    private int $readPos = 0;

    private bool $eof = false;

    public private(set) string $written = '';

    private int $readCount = 0;

    /**
     * @param int $timeoutAfterReads After this many successful reads, throw TimeoutException. 0 means throw immediately.
     */
    public function __construct(
        private readonly string $data = '',
        private readonly int $timeoutAfterReads = 0,
    ) {}

    public function reachedEndOfDataSource(): bool
    {
        return $this->eof;
    }

    public function tryRead(null|int $maxBytes = null): string
    {
        return $this->read($maxBytes);
    }

    public function read(
        null|int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        if ($this->readCount >= $this->timeoutAfterReads) {
            throw new IO\Exception\RuntimeException('Read timed out.');
        }

        if ($this->readPos >= strlen($this->data)) {
            $this->eof = true;

            return '';
        }

        $this->readCount++;

        /** @var int<0, max> $readPos */
        $readPos = $this->readPos;
        $remaining = strlen($this->data) - $readPos;
        /** @var non-negative-int $size */
        $size = $maxBytes !== null ? min($maxBytes, $remaining) : $remaining;
        $chunk = substr($this->data, $readPos, $size);
        $this->readPos += strlen($chunk);

        return $chunk;
    }

    public function readAll(
        null|int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        /** @var int<0, max> $readPos */
        $readPos = $this->readPos;
        $remaining = substr($this->data, $readPos);
        $this->readPos = strlen($this->data);
        $this->eof = true;

        return $remaining;
    }

    public function readFixedSize(
        int $size,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        return $this->read($size, $cancellation);
    }

    public function tryWrite(string $bytes): int
    {
        $this->written .= $bytes;

        return strlen($bytes);
    }

    public function write(string $bytes, CancellationTokenInterface $cancellation = new NullCancellationToken()): int
    {
        return $this->tryWrite($bytes);
    }

    public function writeAll(
        string $bytes,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): void {
        $this->written .= $bytes;
    }
}
