<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl;
use Psl\Exception\InvariantViolationException;
use Psl\Math;
use Psl\Str;
use Psl\Str\Byte;

final class MemoryHandle implements CloseSeekReadWriteHandleInterface
{
    use WriteHandleConvenienceMethodsTrait;
    use ReadHandleConvenienceMethodsTrait;

    private string $buffer;
    private int $offset = 0;
    private bool $closed = false;

    public function __construct(string $buffer = '')
    {
        $this->buffer = $buffer;
    }

    /**
     * Read from the handle.
     *
     * @param int|null $max_bytes the maximum number of bytes to read.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws InvariantViolationException If $max_bytes is 0.
     *
     * @return string the read data on success, or an empty string if the end of file is reached.
     */
    public function readImmediately(?int $max_bytes = null): string
    {
        $this->assertHandleIsOpen();

        $max_bytes ??= Math\INT64_MAX;

        Psl\invariant($max_bytes > 0, '$max_bytes must be null or positive.');
        $length = Byte\length($this->buffer);
        if ($this->offset >= $length) {
            return '';
        }

        $length = (int) Math\minva($max_bytes, $length - $this->offset);
        $result = Byte\slice($this->buffer, $this->offset, $length);
        $this->offset += $length;

        return $result;
    }

    /**
     * Read from the handle.
     *
     * @param int|null $max_bytes the maximum number of bytes to read.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws InvariantViolationException If $max_bytes is 0.
     *
     * @return string the read data on success, or an empty string if the end of file is reached.
     */
    public function read(?int $max_bytes = null, ?int $timeout_ms = null): string
    {
        return $this->readImmediately($max_bytes);
    }

    /**
     * Move to a specific offset within a handle.
     *
     * Offset is relative to the start of the handle - so, the beginning of the
     * handle is always offset 0.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws InvariantViolationException If $offset is negative.
     */
    public function seek(int $offset): void
    {
        $this->assertHandleIsOpen();

        Psl\invariant($offset >= 0, '$offset must be a positive-int.');
        $this->offset = $offset;
    }

    /**
     * Get the current pointer position within a handle.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     */
    public function tell(): int
    {
        $this->assertHandleIsOpen();

        return $this->offset;
    }

    /**
     * Write data.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     *
     * @return int the number of bytes written on success.
     */
    public function writeImmediately(string $bytes, ?int $timeout_ms = null): int
    {
        $this->assertHandleIsOpen();
        $length = Byte\length($this->buffer);
        if ($length < $this->offset) {
            /** @psalm-suppress MissingThrowsDocblock */
            $this->buffer .= Str\repeat("\0", $this->offset - $length);
            $length = $this->offset;
        }

        $bytes_length = Byte\length($bytes);
        /** @psalm-suppress MissingThrowsDocblock */
        $new = Byte\slice($this->buffer, 0, $this->offset) . $bytes;
        if ($this->offset < $length) {
            /** @psalm-suppress MissingThrowsDocblock */
            $new .= Byte\slice($this->buffer, (int) Math\minva($this->offset + $bytes_length, $length));
        }

        $this->buffer = $new;
        $this->offset += $bytes_length;
        return $bytes_length;
    }

    /**
     * Write data.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     *
     * @return int the number of bytes written on success.
     */
    public function write(string $bytes, ?int $timeout_ms = null): int
    {
        return $this->writeImmediately($bytes);
    }

    /**
     * Close the handle.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     */
    public function close(): void
    {
        $this->assertHandleIsOpen();

        $this->closed = true;
        $this->offset = -1;
    }

    public function getBuffer(): string
    {
        return $this->buffer;
    }

    /**
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     */
    private function assertHandleIsOpen(): void
    {
        if ($this->closed) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }
    }
}
