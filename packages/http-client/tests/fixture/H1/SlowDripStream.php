<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Fixture\H1;

use Psl\Async;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\DateTime\Duration;
use Psl\Network;
use Psl\Str\Byte;

use function min;
use function strlen;
use function substr;

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
        return $this->readPos >= Byte\length($this->input);
    }

    public function tryRead(null|int $maxBytes = null): string
    {
        $remaining = Byte\length($this->input) - $this->readPos;
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
        $remaining = Byte\length($this->input) - $this->readPos;
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
        $this->readPos = Byte\length($this->input);

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

        return Byte\length($bytes);
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
