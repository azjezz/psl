<?php

declare(strict_types=1);

namespace Psl\Tests\Fixture;

use Psl\DateTime\Duration;
use Psl\IO;
use Psl\Math;
use Psl\Str\Byte;

/**
 * Simulates a slow TCP socket that delivers at most $maxBytesPerRead bytes per read() call.
 *
 * This exercises partial-read recovery paths in stream protocols that expect
 * fixed-size headers (e.g., 4-byte frame length prefixes).
 */
final class TrickleReadHandle implements IO\ReadHandleInterface
{
    private string $buffer;
    private int $offset = 0;

    /**
     * @param positive-int $maxBytesPerRead Maximum bytes returned per read() call.
     */
    public function __construct(
        string $data,
        private readonly int $maxBytesPerRead = 1,
    ) {
        $this->buffer = $data;
    }

    public function reachedEndOfDataSource(): bool
    {
        return $this->offset >= Byte\length($this->buffer);
    }

    public function tryRead(null|int $max_bytes = null): string
    {
        return $this->readChunk($max_bytes);
    }

    public function read(null|int $max_bytes = null, null|Duration $timeout = null): string
    {
        return $this->readChunk($max_bytes);
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
                throw new IO\Exception\RuntimeException('TrickleReadHandle: not enough data for readFixedSize.');
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

        $limit = $this->maxBytesPerRead;
        if ($max_bytes !== null) {
            $limit = Math\minva($limit, $max_bytes);
        }

        /** @var non-negative-int $toRead */
        $toRead = Math\minva($limit, $available);
        $chunk = Byte\slice($this->buffer, $this->offset, $toRead);
        $this->offset += $toRead;

        return $chunk;
    }
}
