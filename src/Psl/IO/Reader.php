<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl;
use Psl\Exception\InvariantViolationException;
use Psl\Str;

final class Reader implements ReadHandleInterface
{
    private ReadHandleInterface $handle;

    private bool $eof = false;
    private string $buffer = '';

    public function __construct(ReadHandleInterface $handle)
    {
        $this->handle = $handle;
    }

    public function getHandle(): ReadHandleInterface
    {
        return $this->handle;
    }

    /**
     * An immediate, unordered read.
     *
     * @param int|null $max_bytes the maximum number of bytes to read.
     *
     * up to `$max_bytes` may be allocated in a buffer; large values may lead
     * to unnecessarily hitting the request memory limit.
     *
     * @returns string the read data on success, or an empty string if the end of file is reached.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the read would block.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws InvariantViolationException If $max_bytes is 0.
     */
    public function read(?int $max_bytes = null): string
    {
        Psl\invariant($max_bytes === null || $max_bytes >= 0, '$max_bytes must be null, or >= 0');

        if ($this->eof) {
            return '';
        }

        if ($this->buffer === '') {
            $this->buffer = $this->handle->read();
        }

        if ($this->buffer === '') {
            $this->eof = true;

            return '';
        }

        $buffer = $this->buffer;
        if (null === $max_bytes || $max_bytes >= Str\Byte\length($buffer)) {
            $this->buffer = '';

            return $buffer;
        }

        $this->buffer = Str\Byte\slice($buffer, $max_bytes);

        return Str\Byte\slice($buffer, 0, $max_bytes);
    }

    /**
     * @returns string the read data on success,
     *  or null if the end of file is reached before finding $suffix.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the read would block.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public function readUntil(string $suffix): ?string
    {
        $buf = $this->buffer;
        $idx = Str\Byte\search($buf, $suffix);
        $suffix_len = Str\Byte\length($suffix);
        if ($idx !== null) {
            $this->buffer = Str\Byte\slice($buf, $idx + $suffix_len);
            return Str\Byte\slice($buf, 0, $idx);
        }

        do {
            $chunk = $this->handle->read();
            if ($chunk === '') {
                $this->buffer = $buf;
                return null;
            }
            $buf .= $chunk;
        } while (!Str\Byte\contains($chunk, $suffix));

        $idx = Str\Byte\search($buf, $suffix);
        Psl\invariant($idx !== null, 'Should not have exited loop without suffix');
        $this->buffer = Str\Byte\slice($buf, $idx + $suffix_len);
        return Str\Byte\slice($buf, 0, $idx);
    }

    /**
     * Read fixed amount of bytes specified by $size.
     *
     * @param positive-int $size The number of bytes to read.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the read would block.
     * @throws Exception\RuntimeException If an error occurred during the operation,
     *                                    or reached end of file before requested size.
     * @throws InvariantViolationException If $size is not positive.
     */
    public function readFixedSize(int $size): string
    {
        /** @psalm-suppress RedundantCondition */
        Psl\invariant($size > 0, '$size should be a positive integer.');

        while (Str\Byte\length($this->buffer) < $size && !$this->eof) {
            $chunk = $this->getHandle()->read($size - Str\Byte\length($this->buffer));
            if ($chunk === '') {
                $this->eof = true;
            }

            $this->buffer .= $chunk;
        }

        if ($this->eof) {
            throw new Exception\RuntimeException('Reached end of file before requested size.', 4);
        }

        $buffer_size = Str\Byte\length($this->buffer);
        Psl\invariant($buffer_size >= $size, 'Should have read the requested data or reached EOF');
        if ($size === $buffer_size) {
            $ret = $this->buffer;
            $this->buffer = '';
            return $ret;
        }

        $ret = Str\Byte\slice($this->buffer, 0, $size);
        $this->buffer = Str\Byte\slice($this->buffer, $size);
        return $ret;
    }

    /**
     * Read exactly one byte.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the read would block.
     * @throws Exception\RuntimeException If an error occurred during the operation, or reached end of file.
     */
    public function readByte(): string
    {
        /** @psalm-suppress MissingThrowsDocblock - $size is positive */
        return $this->readFixedSize(1);
    }

    /**
     * @returns string the read data on success,
     *  or null if the end of file is reached before finding the current line terminator.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the read would block.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     */
    public function readLine(): ?string
    {
        $line = $this->readUntil("\n");
        if (null !== $line) {
            return $line;
        }

        /** @psalm-suppress MissingThrowsDocblock - $size is positive */
        $content = $this->read();
        return '' === $content ? null : $content;
    }

    /**
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the read would block.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     *
     * @return bool true if EOL has been reached, false otherwise.
     */
    public function isEndOfFile(): bool
    {
        if ($this->eof) {
            return true;
        }

        if ($this->buffer !== '') {
            return false;
        }

        /** @psalm-suppress MissingThrowsDocblock - $size is positive */
        $this->buffer = $this->handle->read();
        if ($this->buffer === '') {
            $this->eof = true;
            return true;
        }

        return false;
    }
}
