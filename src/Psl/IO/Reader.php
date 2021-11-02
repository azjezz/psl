<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl;
use Psl\Exception\InvariantViolationException;
use Psl\Str;

use function strlen;
use function strpos;
use function substr;

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

    /**
     * Read fixed amount of bytes specified by $size.
     *
     * @param int $size The number of bytes to read.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the read would block.
     * @throws Exception\RuntimeException If an error occurred during the operation,
     *                                    or reached end of file before requested size.
     * @throws InvariantViolationException If $size is not positive.
     */
    public function readFixedSize(int $size, ?int $timeout_ms = null): string
    {
        $timer = new Internal\OptionalIncrementalTimeout(
            $timeout_ms,
            function (): void {
                // @codeCoverageIgnoreStart

                throw new Exception\TimeoutException(Str\format(
                    "Reached timeout before reading requested amount of data",
                    $this->buffer === '' ? 'any' : 'all',
                ));
                // @codeCoverageIgnoreEnd
            },
        );

        while (strlen($this->buffer) < $size && !$this->eof) {
            $this->fillBuffer(
                $size - strlen($this->buffer),
                $timer->getRemaining(),
            );
        }

        if ($this->eof) {
            throw new Exception\RuntimeException('Reached end of file before requested size.');
        }

        $buffer_size = strlen($this->buffer);
        Psl\invariant($buffer_size >= $size, "Should have read the requested data or reached EOF");

        if ($size === $buffer_size) {
            $ret = $this->buffer;
            $this->buffer = '';
            return $ret;
        }

        $ret = substr($this->buffer, 0, $size);
        $this->buffer = substr($this->buffer, $size);
        return $ret;
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
            // @codeCoverageIgnoreStart
            $this->fillBuffer(null, $timeout_ms);
            // @codeCoverageIgnoreEnd
        }

        if ($this->buffer === '') {
            // @codeCoverageIgnoreStart
            throw new Exception\RuntimeException('Reached EOF without any more data.');
            // @codeCoverageIgnoreEnd
        }

        $ret = $this->buffer[0];
        if ($ret === $this->buffer) {
            // @codeCoverageIgnoreStart
            $this->buffer = '';
            $this->eof = true;
            return $ret;
            // @codeCoverageIgnoreEnd
        }

        $this->buffer = substr($this->buffer, 1);
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
        $idx = strpos($buf, $suffix);
        $suffix_len = strlen($suffix);
        if ($idx !== false) {
            $this->buffer = substr($buf, $idx + $suffix_len);
            return substr($buf, 0, $idx);
        }

        do {
            // + 1 as it would have been matched in the previous iteration if it
            // fully fit in the chunk
            $offset = strlen($buf) - $suffix_len + 1;
            $offset = $offset > 0 ? $offset : 0;
            $chunk = $this->handle->read();
            if ($chunk === '') {
                $this->buffer = $buf;
                return null;
            }
            $buf .= $chunk;
            $idx = strpos($buf, $suffix, $offset);
        } while ($idx === false);

        $this->buffer = substr($buf, $idx + $suffix_len);

        return substr($buf, 0, $idx);
    }

    /**
     * Read from the handle, waiting for data if necessary.
     *
     * @param ?int $max_bytes the maximum number of bytes to read
     *
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\TimeoutException If $timeout_ms is reached before being able to read from the handle.
     * @throws InvariantViolationException If $max_bytes is 0.
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the read would block.
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
     * An immediate, unordered read.
     *
     * Up to `$max_bytes` may be allocated in a buffer; large values may lead to
     * unnecessarily hitting the request memory limit.
     *
     * @param ?int $max_bytes the maximum number of bytes to read
     *
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws InvariantViolationException If $max_bytes is 0.
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the read would block.
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

        // @codeCoverageIgnoreStart
        if ($this->buffer === '') {
            $this->buffer = $this->getHandle()->readImmediately();
            if ($this->buffer === '') {
                $this->eof = true;
                return '';
            }
        }
        // @codeCoverageIgnoreEnd

        $buffer = $this->buffer;
        if ($max_bytes === null || $max_bytes >= strlen($buffer)) {
            $this->buffer = '';
            return $buffer;
        }

        $this->buffer = substr($buffer, $max_bytes);

        return substr($buffer, 0, $max_bytes);
    }

    public function getHandle(): ReadHandleInterface
    {
        return $this->handle;
    }

    /**
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the read would block.
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

        // @codeCoverageIgnoreStart
        try {
            // Calling the immediate (but still non-blocking) version as the async
            // version could wait for the other end to send data - which could lead
            // to both ends of a pipe/socket waiting on each other.
            $this->buffer = $this->handle->read();
            if ($this->buffer === '') {
                $this->eof = true;
                return true;
            }
        } catch (Exception\BlockingException) {
            return false;
        } catch (Exception\ExceptionInterface) {
            // ignore; it'll be thrown again when attempting a real read.
        }
        // @codeCoverageIgnoreEnd

        return false;
    }
}
