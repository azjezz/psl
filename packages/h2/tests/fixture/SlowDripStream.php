<?php

declare(strict_types=1);

namespace Psl\H2\Tests\Fixture;

use Psl\Async;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\DateTime\Duration;
use Psl\Network;

use function min;
use function strlen;
use function substr;

/**
 * Simulates a slow-loris attack: delivers data one byte at a time with a
 * configurable delay between each byte.
 *
 * Each individual read completes quickly enough to not trigger a per-read
 * timeout, but the cumulative wall-clock time for reading the full payload
 * exceeds a given total timeout — which is exactly the bug that
 * OptionalIncrementalTimeout is designed to catch.
 */
final class SlowDripStream implements Network\StreamInterface
{
    private int $readPos = 0;

    public private(set) string $written = '';

    public function __construct(
        private readonly string $input,
        private readonly Duration $delayPerByte,
    ) {}

    public function getLocalAddress(): Network\Address
    {
        return Network\Address::tcp('127.0.0.1', 8080);
    }

    public function getPeerAddress(): Network\Address
    {
        return Network\Address::tcp('127.0.0.1', 12_345);
    }

    public function peek(int $maxBytes, CancellationTokenInterface $cancellation = new NullCancellationToken()): string
    {
        $remaining = strlen($this->input) - $this->readPos;
        if ($remaining <= 0) {
            return '';
        }

        /** @var int<0, max> $readPos */
        $readPos = $this->readPos;

        return substr($this->input, $readPos, min(1, $remaining));
    }

    public function shutdown(): void {}

    public function reachedEndOfDataSource(): bool
    {
        return $this->readPos >= strlen($this->input);
    }

    public function tryRead(null|int $maxBytes = null): string
    {
        $remaining = strlen($this->input) - $this->readPos;
        if ($remaining <= 0) {
            return '';
        }

        /** @var int<0, max> $readPos */
        $readPos = $this->readPos;
        $chunk = substr($this->input, $readPos, 1);
        $this->readPos += 1;

        return $chunk;
    }

    public function read(
        null|int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        $remaining = strlen($this->input) - $this->readPos;
        if ($remaining <= 0) {
            return '';
        }

        $cancellation->throwIfCancelled();

        Async\sleep($this->delayPerByte);

        /** @var int<0, max> $readPos */
        $readPos = $this->readPos;
        $chunk = substr($this->input, $readPos, 1);
        $this->readPos += 1;

        return $chunk;
    }

    public function readAll(
        null|int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        /** @var int<0, max> $readPos */
        $readPos = $this->readPos;
        $remaining = substr($this->input, $readPos);
        $this->readPos = strlen($this->input);

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

    public function getStream(): mixed
    {
        return null;
    }

    public function isClosed(): bool
    {
        return false;
    }

    public function close(): void {}
}
