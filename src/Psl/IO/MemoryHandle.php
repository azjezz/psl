<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\Math;

use function str_repeat;
use function strlen;
use function substr;

final class MemoryHandle implements CloseSeekReadWriteHandleInterface
{
    use WriteHandleConvenienceMethodsTrait;
    use ReadHandleConvenienceMethodsTrait;

    /**
     * @var int<0, max>
     */
    private int $offset = 0;
    private string $buffer;
    private bool $closed = false;

    public function __construct(string $buffer = '')
    {
        $this->buffer = $buffer;
    }

    /**
     * Read from the handle.
     *
     * @param positive-int|null $max_bytes the maximum number of bytes to read.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     *
     * @return string the read data on success, or an empty string if the end of file is reached.
     */
    public function tryRead(?int $max_bytes = null): string
    {
        $this->assertHandleIsOpen();

        if (null === $max_bytes) {
            $max_bytes = Math\INT64_MAX;
        }

        $length = strlen($this->buffer);
        if ($this->offset >= $length) {
            return '';
        }

        $length -= $this->offset;
        $length = $length > $max_bytes ? $max_bytes : $length;
        $result = substr($this->buffer, $this->offset, $length);
        $this->offset = ($offset = $this->offset + $length) >= 0 ? $offset : 0;

        return $result;
    }

    /**
     * Read from the handle.
     *
     * @param positive-int|null $max_bytes the maximum number of bytes to read.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     *
     * @return string the read data on success, or an empty string if the end of file is reached.
     */
    public function read(?int $max_bytes = null, ?float $timeout = null): string
    {
        return $this->tryRead($max_bytes);
    }

    /**
     * {@inheritDoc}
     */
    public function seek(int $offset): void
    {
        $this->assertHandleIsOpen();

        $this->offset = $offset;
    }

    /**
     * {@inheritDoc}
     */
    public function tell(): int
    {
        $this->assertHandleIsOpen();

        return $this->offset;
    }

    /**
     * {@inheritDoc}
     */
    public function tryWrite(string $bytes, ?float $timeout = null): int
    {
        $this->assertHandleIsOpen();
        $length = strlen($this->buffer);
        if ($length < $this->offset) {
            $this->buffer .= str_repeat("\0", $this->offset - $length);
            $length = $this->offset;
        }

        $bytes_length = strlen($bytes);
        $new = substr($this->buffer, 0, $this->offset) . $bytes;
        if ($this->offset < $length) {
            $offset = $this->offset + $bytes_length;
            $offset = $offset > $length ? $length : $offset;
            $new .= substr($this->buffer, $offset);
        }

        $this->buffer = $new;
        $this->offset = ($offset = $this->offset + $bytes_length) >= 0 ? $offset : 0;
        return $bytes_length;
    }

    /**
     * {@inheritDoc}
     */
    public function write(string $bytes, ?float $timeout = null): int
    {
        return $this->tryWrite($bytes);
    }

    /**
     * {@inheritDoc}
     */
    public function close(): void
    {
        $this->closed = true;
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
