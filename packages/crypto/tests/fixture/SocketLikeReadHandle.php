<?php

declare(strict_types=1);

namespace Psl\Crypto\Tests\Fixture;

use Psl\Async;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO;
use Psl\Math;
use Psl\Str\Byte;

/**
 * Simulates a TCP socket: delivers all buffered data but never reports EOF.
 * When no data remains, read() throws CancelledException (simulating a blocking read that would deadlock).
 */
final class SocketLikeReadHandle implements IO\ReadHandleInterface
{
    private string $buffer;
    private int $offset = 0;

    public function __construct(string $data)
    {
        $this->buffer = $data;
    }

    public function reachedEndOfDataSource(): bool
    {
        return false;
    }

    public function tryRead(null|int $maxBytes = null): string
    {
        return $this->readChunk($maxBytes);
    }

    public function read(
        null|int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        $chunk = $this->readChunk($maxBytes);
        if ($chunk === '') {
            throw new Async\Exception\CancelledException($cancellation);
        }

        return $chunk;
    }

    public function readAll(
        null|int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        $remaining = Byte\slice($this->buffer, $this->offset);
        $this->offset = Byte\length($this->buffer);

        return $remaining;
    }

    public function readFixedSize(
        int $size,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        $result = '';
        while (Byte\length($result) < $size) {
            $chunk = $this->readChunk($size - Byte\length($result));
            if ($chunk === '') {
                throw new Async\Exception\CancelledException($cancellation);
            }

            $result .= $chunk;
        }

        return $result;
    }

    private function readChunk(null|int $maxBytes): string
    {
        $available = Byte\length($this->buffer) - $this->offset;
        if ($available <= 0) {
            return '';
        }

        /** @var non-negative-int $toRead */
        $toRead = $maxBytes !== null ? Math\minva($maxBytes, $available) : $available;
        $chunk = Byte\slice($this->buffer, $this->offset, $toRead);
        $this->offset += $toRead;

        return $chunk;
    }
}
