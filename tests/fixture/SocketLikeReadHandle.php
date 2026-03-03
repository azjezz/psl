<?php

declare(strict_types=1);

namespace Psl\Tests\Fixture;

use Psl\DateTime\Duration;
use Psl\IO;
use Psl\Math;
use Psl\Str\Byte;

/**
 * Simulates a TCP socket: delivers all buffered data but never reports EOF.
 * When no data remains, read() throws TimeoutException (simulating a blocking read that would deadlock).
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

    public function tryRead(null|int $max_bytes = null): string
    {
        return $this->readChunk($max_bytes);
    }

    public function read(null|int $max_bytes = null, null|Duration $timeout = null): string
    {
        $chunk = $this->readChunk($max_bytes);
        if ($chunk === '') {
            throw new IO\Exception\TimeoutException('Socket would block: no more data available (deadlock).');
        }

        return $chunk;
    }

    public function readAll(null|int $max_bytes = null, null|Duration $timeout = null): string
    {
        $remaining = Byte\slice($this->buffer, $this->offset);
        $this->offset = Byte\length($this->buffer);

        return $remaining;
    }

    public function readFixedSize(int $size, null|Duration $timeout = null): string
    {
        $result = '';
        while (Byte\length($result) < $size) {
            $chunk = $this->readChunk($size - Byte\length($result));
            if ($chunk === '') {
                throw new IO\Exception\TimeoutException(
                    'Socket would block: not enough data for readFixedSize (deadlock).',
                );
            }

            $result .= $chunk;
        }

        return $result;
    }

    private function readChunk(null|int $max_bytes): string
    {
        $available = Byte\length($this->buffer) - $this->offset;
        if ($available <= 0) {
            return '';
        }

        /** @var non-negative-int $toRead */
        $toRead = $max_bytes !== null ? Math\minva($max_bytes, $available) : $available;
        $chunk = Byte\slice($this->buffer, $this->offset, $toRead);
        $this->offset += $toRead;

        return $chunk;
    }
}
