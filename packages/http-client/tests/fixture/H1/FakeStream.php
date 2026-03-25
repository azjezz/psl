<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Fixture\H1;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO;
use Psl\Network;

use function min;
use function strlen;
use function substr;

final class FakeStream implements Network\StreamInterface
{
    private int $readPos = 0;

    private bool $closed = false;

    public private(set) string $written = '';

    public function __construct(
        private readonly string $input,
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
        if ($this->closed) {
            throw new IO\Exception\AlreadyClosedException('Stream is closed');
        }

        $remaining = strlen($this->input) - $this->readPos;
        if ($remaining <= 0) {
            return '';
        }

        /** @var int<0, max> $readPos */
        $readPos = $this->readPos;
        $len = min($maxBytes, $remaining);

        return substr($this->input, $readPos, $len);
    }

    public function shutdown(): void {}

    public function reachedEndOfDataSource(): bool
    {
        return $this->readPos >= strlen($this->input);
    }

    public function tryRead(null|int $maxBytes = null): string
    {
        return $this->read($maxBytes);
    }

    public function read(
        null|int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        if ($this->closed) {
            throw new IO\Exception\AlreadyClosedException('Stream is closed');
        }

        $remaining = strlen($this->input) - $this->readPos;
        if ($remaining <= 0) {
            return '';
        }

        /** @var int<0, max> $readPos */
        $readPos = $this->readPos;
        $len = $maxBytes !== null ? min($maxBytes, $remaining) : $remaining;
        $chunk = substr($this->input, $readPos, $len);
        $this->readPos += strlen($chunk);

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
        return $this->closed;
    }

    public function close(): void
    {
        $this->closed = true;
    }
}
