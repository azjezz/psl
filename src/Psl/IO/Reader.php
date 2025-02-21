<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\Async;
use Psl\DateTime\Duration;
use Psl\Str;

use function strlen;
use function strpos;
use function substr;

use const PHP_EOL;

final class Reader implements ReadHandleInterface
{
    use ReadHandleConvenienceMethodsTrait;

    private readonly ReadHandleInterface $handle;

    private bool $eof = false;
    private string $buffer = '';

    public function __construct(ReadHandleInterface $handle)
    {
        $this->handle = $handle;
    }

    /**
     * {@inheritDoc}
     *
     * @mago-ignore best-practices/no-empty-catch-clause
     */
    #[\Override]
    public function reachedEndOfDataSource(): bool
    {
        if ($this->eof) {
            return true;
        }

        if ($this->buffer !== '') {
            return false;
        }

        // @codeCoverageIgnoreStart
        try {
            $this->buffer = $this->handle->read();
            if ($this->buffer === '') {
                return $this->eof = $this->handle->reachedEndOfDataSource();
            }
        } catch (Exception\ExceptionInterface) {
            // ignore; it'll be thrown again when attempting a real read.
        }
        // @codeCoverageIgnoreEnd

        return false;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function readFixedSize(int $size, null|Duration $timeout = null): string
    {
        $timer = new Async\OptionalIncrementalTimeout($timeout, function (): void {
            // @codeCoverageIgnoreStart
            throw new Exception\TimeoutException(Str\format(
                'Reached timeout before reading requested amount of data',
                $this->buffer === '' ? 'any' : 'all',
            ));
            // @codeCoverageIgnoreEnd
        });

        do {
            $length = strlen($this->buffer);
            if ($length >= $size || $this->eof) {
                break;
            }

            /** @var positive-int $to_read */
            $to_read = $size - $length;
            $this->fillBuffer($to_read, $timer->getRemaining());
        } while (true);

        if ($this->eof) {
            throw new Exception\RuntimeException('Reached end of file before requested size.');
        }

        $buffer_size = strlen($this->buffer);
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
     * Read a single byte from the handle.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation, or reached end of file.
     * @throws Exception\TimeoutException If $timeout is reached before being able to read from the handle.
     */
    public function readByte(null|Duration $timeout = null): string
    {
        if ($this->buffer === '' && !$this->eof) {
            $this->fillBuffer(null, $timeout);
        }

        if ($this->buffer === '') {
            throw new Exception\RuntimeException('Reached EOF without any more data.');
        }

        $ret = $this->buffer[0];
        if ($ret === $this->buffer) {
            $this->buffer = '';
            return $ret;
        }

        $this->buffer = substr($this->buffer, 1);
        return $ret;
    }

    /**
     * @returns string the read data on success,
     *  or null if the end of file is reached before finding the current line terminator.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\TimeoutException If $timeout is reached before being able to read from the handle.
     */
    public function readLine(null|Duration $timeout = null): null|string
    {
        $timer = new Async\OptionalIncrementalTimeout($timeout, static function (): void {
            // @codeCoverageIgnoreStart
            throw new Exception\TimeoutException(
                'Reached timeout before encountering reaching the current line terminator.',
            );
            // @codeCoverageIgnoreEnd
        });

        $line = $this->readUntil(PHP_EOL, $timer->getRemaining());
        if (null !== $line) {
            return $line;
        }

        /** @psalm-suppress MissingThrowsDocblock - $size is positive */
        $content = $this->read(null, $timer->getRemaining());
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
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\TimeoutException If $timeout is reached before being able to read from the handle.
     *
     * @mago-ignore best-practices/no-boolean-literal-comparison
     */
    public function readUntil(string $suffix, null|Duration $timeout = null): null|string
    {
        $buf = $this->buffer;
        $idx = strpos($buf, $suffix);
        $suffix_len = strlen($suffix);
        if ($idx !== false) {
            $this->buffer = substr($buf, $idx + $suffix_len);
            return substr($buf, 0, $idx);
        }

        $timer = new Async\OptionalIncrementalTimeout($timeout, static function () use ($suffix): void {
            // @codeCoverageIgnoreStart
            throw new Exception\TimeoutException(Str\format(
                "Reached timeout before encountering the suffix (\"%s\").",
                $suffix,
            ));
            // @codeCoverageIgnoreEnd
        });

        do {
            // + 1 as it would have been matched in the previous iteration if it
            // fully fit in the chunk
            $offset = (strlen($buf) - $suffix_len) + 1;
            $offset = $offset > 0 ? $offset : 0;
            $chunk = $this->handle->read(null, $timer->getRemaining());
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
     * {@inheritDoc}
     */
    #[\Override]
    public function read(null|int $max_bytes = null, null|Duration $timeout = null): string
    {
        if ($this->eof) {
            return '';
        }

        if ($this->buffer === '') {
            $this->fillBuffer(null, $timeout);
        }

        // We either have a buffer, or reached EOF; either way, behavior matches
        // read, so just delegate
        return $this->tryRead($max_bytes);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function tryRead(null|int $max_bytes = null): string
    {
        if ($this->eof) {
            return '';
        }

        if ($this->buffer === '') {
            $this->buffer = $this->getHandle()->tryRead();
            if ($this->buffer === '') {
                return '';
            }
        }

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
     * @param null|positive-int $desired_bytes
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\TimeoutException If $timeout is reached before being able to read from the handle.
     */
    private function fillBuffer(null|int $desired_bytes, null|Duration $timeout): void
    {
        $chunk = $this->handle->read($desired_bytes, $timeout);
        $this->buffer .= $chunk;
        if ($chunk === '') {
            $this->eof = $this->handle->reachedEndOfDataSource();
        }
    }
}
