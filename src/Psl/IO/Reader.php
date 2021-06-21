<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl;
use Psl\Exception\InvariantViolationException;
use Psl\Math;
use Psl\Str;

final class Reader implements ReadHandleInterface
{
    use ReadHandleConvenienceMethodsTrait;

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
     * Up to `$max_bytes` may be allocated in a buffer; large values may lead to
     * unnecessarily hitting the request memory limit.
     *
     * @param ?int $max_bytes the maximum number of bytes to read
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the read would block.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws InvariantViolationException If $max_bytes is 0.
     *
     * @return string the read data on success, or an empty string if the end of file is reached.
     *
     * @see ReadHandleInterface::read()
     * @see ReadHandleInterface::readAll()
     */
    public function readImmediately(?int $max_bytes = null): string
    {
        Psl\invariant(
            $max_bytes === null || $max_bytes > 0,
            '$max_bytes must be null, or greater than 0',
        );

        if ($this->eof) {
            return '';
        }

        if ($this->buffer === '') {
            $this->buffer = $this->getHandle()->readImmediately();
            if ($this->buffer === '') {
                $this->eof = true;
                return '';
            }
        }

        if ($max_bytes === null || $max_bytes >= Str\Byte\length($this->buffer)) {
            $buf = $this->buffer;
            $this->buffer = '';
            return $buf;
        }

        $buf = $this->buffer;
        $this->buffer = Str\Byte\slice($buf, $max_bytes);

        return Str\Byte\slice($buf, 0, $max_bytes);
    }

    /**
     * Read from the handle, waiting for data if necessary.
     *
     * @param ?int $max_bytes the maximum number of bytes to read
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the read would block.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\TimeoutException If $timeout_ms is reached before being able to read from the handle.
     * @throws InvariantViolationException If $max_bytes is 0.
     *
     * @return string the read data on success, or an empty string if the end of file is reached.
     *
     * Up to `$max_bytes` may be allocated in a buffer; large values may lead to
     * unnecessarily hitting the request memory limit.
     */
    public function read(?int $max_bytes = null, ?int $timeout_ms = null): string
    {
        Psl\invariant($max_bytes === null || $max_bytes >= 0, '$max_bytes must be null, or >= 0');
        Psl\invariant($timeout_ms === null || $timeout_ms > 0, '$timeout_ms must be null, or > 0');

        if ($this->eof) {
            return '';
        }

        if ($this->buffer === '') {
            $this->fillBuffer(null, $timeout_ms);
        }

        // We either have a buffer, or reached EOF; either way, behavior matches
        // read, so just delegate
        return $this->readImmediately($max_bytes);
    }

    /**
     * Read until the specified suffix is seen.
     *
     * The trailing suffix is read (so won't be returned by other calls), but is not
     * included in the return value.
     *
     * This call returns null if the suffix is not seen, even if there is other
     * data.
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
            // + 1 as it would have been matched in the previous iteration if it
            // fully fit in the chunk
            $offset = (int) Math\maxva(0, Str\Byte\length($buf) - $suffix_len + 1);
            $chunk = $this->handle->read();
            if ($chunk === '') {
                $this->buffer = $buf;
                return null;
            }
            $buf .= $chunk;
            $idx = Str\Byte\search($buf, $suffix, $offset);
        } while ($idx === null);

        $this->buffer = Str\Byte\slice($buf, $idx + $suffix_len);

        return Str\Byte\slice($buf, 0, $idx);
    }

    /**
     * Read a fixed amount of data.
     *
     * It is possible for this to never return, e.g. if called on a pipe or
     * or socket which the other end keeps open forever. Set a timeout if you
     * do not want this to happen.
     *
     * @throws Psl\Exception\InvariantViolationException If $size is not positive.
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the read would block.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\TimeoutException If $timeout_ms is reached before being able to read from the handle.
     */
    public function readFixedSize(int $size, ?int $timeout_ms = null): string
    {
        $timer = new Internal\OptionalIncrementalTimeout(
            $timeout_ms,
            static function (): void {
                throw new Exception\TimeoutException(
                    "Reached timeout before reading requested amount of data",
                );
            },
        );

        while (Str\Byte\length($this->buffer) < $size && !$this->eof) {
            $this->fillBuffer(
                $size - Str\Byte\length($this->buffer),
                $timer->getRemaining(),
            );
        }

        if ($this->eof) {
            throw new Exception\RuntimeException('Reached end of file before requested size.');
        }

        $buffer_size = Str\Byte\length($this->buffer);
        Psl\invariant($buffer_size >= $size, "Should have read the requested data or reached EOF");

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
     * Read a single byte from the handle.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the read would block.
     * @throws Exception\RuntimeException If an error occurred during the operation, or reached end of file.
     * @throws Psl\Exception\InvariantViolationException If $timeout_ms is negative.
     */
    public function readByte(?int $timeout_ms = null): string
    {
        Psl\invariant(
            $timeout_ms === null || $timeout_ms > 0,
            '$timeout_ms must be null, or > 0',
        );

        if ($this->buffer === '' && !$this->eof) {
            $this->fillBuffer(null, $timeout_ms);
        }

        if ($this->buffer === '') {
            throw new Exception\RuntimeException('Reached EOF without any more data.');
        }

        $ret = $this->buffer[0];
        if ($ret === $this->buffer) {
            $this->buffer = '';
            $this->eof = true;
            return $ret;
        }

        $this->buffer = Str\Byte\slice($this->buffer, 1);
        return $ret;
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

        try {
            // Calling the immediate (but still non-blocking) version as the async
            // version could wait for the other end to send data - which could lead
            // to both ends of a pipe/socket waiting on each other.
            $this->buffer = $this->handle->readImmediately();
            if ($this->buffer === '') {
                $this->eof = true;
                return true;
            }
        } catch (Exception\BlockingException $_ex) {
            return false;
        } catch (Exception\ExceptionInterface $ex) {
            // ignore; it'll be thrown again when attempting a real read.
        }

        return false;
    }

    /**
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the read would block.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\TimeoutException If $timeout_ms is reached before being able to read from the handle.
     */
    private function fillBuffer(?int $desired_bytes, ?int $timeout_ms): void
    {
        $chunk = $this->handle->read($desired_bytes, $timeout_ms);
        if ($chunk === '') {
            $this->eof = true;
        }

        $this->buffer .= $chunk;
    }
}
